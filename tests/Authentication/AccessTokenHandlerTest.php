<?php

namespace Tests\Authentication;

use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Services;
use Sparks\Shield\Authentication\Authentication;
use Sparks\Shield\Authentication\Handlers\AccessTokens;
use Sparks\Shield\Config\Auth;
use Sparks\Shield\Entities\AccessToken;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Models\UserModel;
use Sparks\Shield\Result;

/**
 * @internal
 */
final class AccessTokenHandlerTest extends CIDatabaseTestCase
{
    protected AccessTokens $auth;
    protected $namespace = '\Sparks\Shield';

    protected function setUp(): void
    {
        parent::setUp();

        $config = new Auth();
        $auth   = new Authentication($config);
        $auth->setProvider(model(UserModel::class)); // @phpstan-ignore-line

        /** @var AccessTokens $handler */
        $handler    = $auth->factory('tokens');
        $this->auth = $handler;

        Services::injectMock('events', new MockEvents());
    }

    public function testLogin()
    {
        $user = fake(UserModel::class);

        $this->auth->login($user);

        // Stores the user
        $this->assertInstanceOf(User::class, $this->auth->getUser());
        $this->assertSame($user->id, $this->auth->getUser()->getAuthId());
    }

    public function testLogout()
    {
        // this one's a little odd since it's stateless, but roll with it...
        $user = fake(UserModel::class);

        $this->auth->login($user);
        $this->assertNotNull($this->auth->getUser());

        $this->auth->logout();
        $this->assertNull($this->auth->getUser());
    }

    public function testLoginByidNoToken()
    {
        $user = fake(UserModel::class);

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertNull($this->auth->getUser()->currentAccessToken());
    }

    public function testLoginByIdWithToken()
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $this->setRequestHeader($token->raw_token);

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertInstanceOf(AccessToken::class, $this->auth->getUser()->currentAccessToken());
        $this->assertSame($token->id, $this->auth->getUser()->currentAccessToken()->id);
    }

    public function testLoginByIdWithMultipleTokens()
    {
        /** @var User $user */
        $user   = fake(UserModel::class);
        $token1 = $user->generateAccessToken('foo');
        $user->generateAccessToken('bar');

        $this->setRequestHeader($token1->raw_token);

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertInstanceOf(AccessToken::class, $this->auth->getUser()->currentAccessToken());
        $this->assertSame($token1->id, $this->auth->getUser()->currentAccessToken()->id);
    }

    public function testCheckNoToken()
    {
        $result = $this->auth->check([]);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.noToken'), $result->reason());
    }

    public function testCheckBadToken()
    {
        $result = $this->auth->check(['token' => 'abc123']);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badToken'), $result->reason());
    }

    public function testCheckSuccess()
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $this->seeInDatabase('auth_identities', [
            'user_id'      => $user->id,
            'type'         => 'access_token',
            'last_used_at' => null,
        ]);

        $result = $this->auth->check(['token' => $token->raw_token]);

        $this->assertTrue($result->isOK());
        $this->assertInstanceOf(User::class, $result->extraInfo());
        $this->assertSame($user->id, $result->extraInfo()->id);

        $updatedToken = $result->extraInfo()->currentAccessToken();
        $this->assertNotEmpty($updatedToken->last_used_at);
    }

    public function testAttemptCannotFindUser()
    {
        $result = $this->auth->attempt([
            'token' => 'abc123',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badToken'), $result->reason());

        // A login attempt should have always been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => 'token: abc123',
            'success' => 0,
        ]);
    }

    public function testAttemptSuccess()
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');
        $this->setRequestHeader($token->raw_token);

        $result = $this->auth->attempt([
            'token' => $token->raw_token,
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertSame($user->id, $foundUser->id);
        $this->assertInstanceOf(AccessToken::class, $foundUser->currentAccessToken());
        $this->assertSame($token->token, $foundUser->currentAccessToken()->token);

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => 'token: ' . $token->raw_token,
            'success' => 1,
        ]);
    }

    protected function setRequestHeader(string $token)
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Bearer ' . $token);
    }
}
