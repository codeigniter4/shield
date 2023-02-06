<?php

declare(strict_types=1);

namespace Tests\Authentication\Authenticators;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Result;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Services;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class AccessTokenAuthenticatorTest extends DatabaseTestCase
{
    private AccessTokens $auth;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new Auth();
        $auth   = new Authentication($config);
        $auth->setProvider(model(UserModel::class));

        /** @var AccessTokens $authenticator */
        $authenticator = $auth->factory('tokens');
        $this->auth    = $authenticator;

        Services::injectMock('events', new MockEvents());
    }

    public function testLogin(): void
    {
        $user = fake(UserModel::class);

        $this->auth->login($user);

        // Stores the user
        $this->assertInstanceOf(User::class, $this->auth->getUser());
        $this->assertSame($user->id, $this->auth->getUser()->id);
    }

    public function testLogout(): void
    {
        // this one's a little odd since it's stateless, but roll with it...
        $user = fake(UserModel::class);

        $this->auth->login($user);
        $this->assertNotNull($this->auth->getUser());

        $this->auth->logout();
        $this->assertNull($this->auth->getUser());
    }

    public function testLoginByIdNoToken(): void
    {
        $user = fake(UserModel::class);

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertNull($this->auth->getUser()->currentAccessToken());
    }

    public function testLoginByIdWithToken(): void
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

    public function testLoginByIdWithMultipleTokens(): void
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

    public function testCheckNoToken(): void
    {
        $result = $this->auth->check([]);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.noToken', [config('Auth')->authenticatorHeader['tokens']]), $result->reason());
    }

    public function testCheckBadToken(): void
    {
        $result = $this->auth->check(['token' => 'abc123']);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badToken'), $result->reason());
    }

    public function testCheckOldToken(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class);
        /** @var UserIdentityModel $identities */
        $identities          = model(UserIdentityModel::class);
        $token               = $user->generateAccessToken('foo');
        $token->last_used_at = Time::now()->subYears(1)->subMinutes(1);
        $identities->save($token);

        $result = $this->auth->check(['token' => $token->raw_token]);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.oldToken'), $result->reason());
    }

    public function testCheckSuccess(): void
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $this->seeInDatabase(SHIELD_TABLES['auth_identities'], [
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

        // Checking token in the same second does not throw "DataException : There is no data to update."
        $this->auth->check(['token' => $token->raw_token]);
    }

    public function testAttemptCannotFindUser(): void
    {
        $result = $this->auth->attempt([
            'token' => 'abc123',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badToken'), $result->reason());

        // A login attempt should have always been recorded
        $this->seeInDatabase('auth_token_logins', [
            'id_type'    => AccessTokens::ID_TYPE_ACCESS_TOKEN,
            'identifier' => 'abc123',
            'success'    => 0,
        ]);
    }

    public function testAttemptSuccess(): void
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
        $this->seeInDatabase('auth_token_logins', [
            'id_type'    => AccessTokens::ID_TYPE_ACCESS_TOKEN,
            'identifier' => $token->raw_token,
            'success'    => 1,
        ]);
    }

    protected function setRequestHeader(string $token): void
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Bearer ' . $token);
    }
}
