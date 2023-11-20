<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Authentication\Authenticators;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\Authenticators\HmacSha256;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Services;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class HmacAuthenticatorTest extends DatabaseTestCase
{
    private HmacSha256 $auth;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new Auth();
        $auth   = new Authentication($config);
        $auth->setProvider(model(UserModel::class));

        config('AuthToken')->recordLoginAttempt = Auth::RECORD_LOGIN_ATTEMPT_ALL;

        /** @var HmacSha256 $authenticator */
        $authenticator = $auth->factory('hmac');
        $this->auth    = $authenticator;

        Services::injectMock('events', new MockEvents());
    }

    public function testLogin(): void
    {
        /** @var User $user */
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
        /** @var User $user */
        $user = fake(UserModel::class);

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertNull($this->auth->getUser()->currentHmacToken());
    }

    public function testLoginByIdBadId(): void
    {
        fake(UserModel::class);

        $this->assertFalse($this->auth->loggedIn());

        try {
            $this->auth->loginById(0);
        } catch (AuthenticationException $e) {
            // Failed login
        }

        $this->assertFalse($this->auth->loggedIn());
        $this->assertNull($this->auth->getUser());
    }

    public function testLoginByIdWithToken(): void
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateHmacToken('foo');

        $rawToken = $this->generateRawHeaderToken($token->secret, $token->secret2, 'bar');
        $this->setRequestHeader($rawToken);

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertInstanceOf(AccessToken::class, $this->auth->getUser()->currentHmacToken());
        $this->assertSame($token->id, $this->auth->getUser()->currentHmacToken()->id);
    }

    public function testLoginByIdWithMultipleTokens(): void
    {
        /** @var User $user */
        $user   = fake(UserModel::class);
        $token1 = $user->generateHmacToken('foo');
        $user->generateHmacToken('bar');

        $this->setRequestHeader($this->generateRawHeaderToken($token1->secret, $token1->secret2, 'bar'));

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertInstanceOf(AccessToken::class, $this->auth->getUser()->currentHmacToken());
        $this->assertSame($token1->id, $this->auth->getUser()->currentHmacToken()->id);
    }

    public function testCheckNoToken(): void
    {
        $result = $this->auth->check([]);

        $this->assertFalse($result->isOK());
        $this->assertSame(
            lang('Auth.noToken', [config('AuthToken')->authenticatorHeader['hmac']]),
            $result->reason()
        );
    }

    public function testCheckBadSignature(): void
    {
        $result = $this->auth->check([
            'token' => 'abc123:lasdkjflksjdflksjdf',
            'body'  => 'bar',
        ]);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badToken'), $result->reason());
    }

    public function testCheckOldToken(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class);
        /** @var UserIdentityModel $identities */
        $identities = model(UserIdentityModel::class);
        $token      = $user->generateHmacToken('foo');
        // CI 4.2 uses the Chicago timezone that has Daylight Saving Time,
        // so subtracts 1 hour to make sure this test passes.
        $token->last_used_at = Time::now()->subYears(1)->subHours(1)->subMinutes(1);
        $identities->save($token);

        $result = $this->auth->check([
            'token' => $this->generateRawHeaderToken($token->secret, $token->secret2, 'bar'),
            'body'  => 'bar',
        ]);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.oldToken'), $result->reason());
    }

    public function testCheckSuccess(): void
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateHmacToken('foo');

        $this->seeInDatabase($this->tables['identities'], [
            'user_id'      => $user->id,
            'type'         => 'hmac_sha256',
            'last_used_at' => null,
        ]);

        $rawToken = $this->generateRawHeaderToken($token->secret, $token->secret2, 'bar');

        $result = $this->auth->check([
            'token' => $rawToken,
            'body'  => 'bar',
        ]);

        $this->assertTrue($result->isOK());
        $this->assertInstanceOf(User::class, $result->extraInfo());
        $this->assertSame($user->id, $result->extraInfo()->id);

        $updatedToken = $result->extraInfo()->currentHmacToken();
        $this->assertNotEmpty($updatedToken->last_used_at);

        // Checking token in the same second does not throw "DataException : There is no data to update."
        $this->auth->check(['token' => $rawToken, 'body' => 'bar']);
    }

    public function testCheckBadToken(): void
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateHmacToken('foo');

        $this->seeInDatabase($this->tables['identities'], [
            'user_id'      => $user->id,
            'type'         => 'hmac_sha256',
            'last_used_at' => null,
        ]);

        $rawToken = $this->generateRawHeaderToken($token->secret, $token->secret2, 'foobar');

        $result = $this->auth->check([
            'token' => $rawToken,
            'body'  => 'bar',
        ]);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badToken'), $result->reason());
    }

    public function testAttemptCannotFindUser(): void
    {
        $result = $this->auth->attempt([
            'token' => 'abc123:lsakdjfljsdflkajsfd',
            'body'  => 'bar',
        ]);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badToken'), $result->reason());

        // A login attempt should have always been recorded
        $this->seeInDatabase($this->tables['token_logins'], [
            'id_type'    => HmacSha256::ID_TYPE_HMAC_TOKEN,
            'identifier' => 'abc123:lsakdjfljsdflkajsfd',
            'success'    => 0,
        ]);
    }

    public function testAttemptSuccess(): void
    {
        /** @var User $user */
        $user     = fake(UserModel::class);
        $token    = $user->generateHmacToken('foo');
        $rawToken = $this->generateRawHeaderToken($token->secret, $token->secret2, 'bar');
        $this->setRequestHeader($rawToken);

        $result = $this->auth->attempt([
            'token' => $rawToken,
            'body'  => 'bar',
        ]);

        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertSame($user->id, $foundUser->id);
        $this->assertInstanceOf(AccessToken::class, $foundUser->currentHmacToken());
        $this->assertSame($token->token, $foundUser->currentHmacToken()->token);

        // A login attempt should have been recorded
        $this->seeInDatabase($this->tables['token_logins'], [
            'id_type'    => HmacSha256::ID_TYPE_HMAC_TOKEN,
            'identifier' => $rawToken,
            'success'    => 1,
        ]);

        // Check get key Method
        $key = $this->auth->getHmacKeyFromToken();
        $this->assertSame($token->secret, $key);

        // Check get hash method
        [, $hash]  = explode(':', $rawToken);
        $secretKey = $this->auth->getHmacHashFromToken();
        $this->assertSame($hash, $secretKey);
    }

    public function testAttemptBanned(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class);
        $user->ban('Test ban.');

        $token    = $user->generateHmacToken('foo');
        $rawToken = $this->generateRawHeaderToken($token->secret, $token->secret2, 'bar');
        $this->setRequestHeader($rawToken);

        $result = $this->auth->attempt([
            'token' => $rawToken,
            'body'  => 'bar',
        ]);

        $this->assertFalse($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertNull($foundUser);

        // A login attempt should have been recorded
        $this->seeInDatabase($this->tables['token_logins'], [
            'id_type'    => HmacSha256::ID_TYPE_HMAC_TOKEN,
            'identifier' => $rawToken,
            'success'    => 0,
        ]);
    }

    protected function setRequestHeader(string $token): void
    {
        $request = service('request');
        $request->setHeader('Authorization', 'HMAC-SHA256 ' . $token);
    }

    protected function generateRawHeaderToken(string $secret, string $secretKey, string $body): string
    {
        return $secret . ':' . hash_hmac('sha256', $body, $secretKey);
    }
}
