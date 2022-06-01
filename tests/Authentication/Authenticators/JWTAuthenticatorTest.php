<?php

namespace Tests\Authentication\Authenticators;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use CodeIgniter\Shield\Authentication\TokenGenerator\JWTGenerator;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Result;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Services;
use Firebase\JWT\JWT as FirebaseJWT;
use InvalidArgumentException;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class JWTAuthenticatorTest extends DatabaseTestCase
{
    private const BAD_JWT = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJJc3N1ZXIgb2YgdGhlIEpXVCIsImF1ZCI6IkF1ZGllbmNlIG9mIHRoZSBKV1QiLCJzdWIiOiIxIiwiaWF0IjoxNjUzOTkxOTg5LCJleHAiOjE2NTM5OTU1ODl9.hgOYHEcT6RGHb3po1lspTcmjrylY1Cy1IvYmHOyx0CY';

    private JWT $auth;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new Auth();
        $auth   = new Authentication($config);
        $auth->setProvider(\model(UserModel::class)); // @phpstan-ignore-line

        /** @var JWT $authenticator */
        $authenticator = $auth->factory('jwt');
        $this->auth    = $authenticator;

        Services::injectMock('events', new MockEvents());
    }

    private function createUser(): User
    {
        return \fake(UserModel::class);
    }

    public function testLogin()
    {
        $user = $this->createUser();

        $this->auth->login($user);

        // Stores the user
        $this->assertInstanceOf(User::class, $this->auth->getUser());
        $this->assertSame($user->id, $this->auth->getUser()->id);
    }

    public function testLogout()
    {
        // this one's a little odd since it's stateless, but roll with it...

        $user = $this->createUser();

        $this->auth->login($user);
        $this->assertNotNull($this->auth->getUser());

        $this->auth->logout();
        $this->assertNull($this->auth->getUser());
    }

    public function testLoginById()
    {
        $user = $this->createUser();

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
    }

    public function testLoginByIdNoUser()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unable to locate the specified user.');

        $this->createUser();

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById(9999);
    }

    public function testCheckNoToken()
    {
        $result = $this->auth->check([]);

        $this->assertFalse($result->isOK());
        $this->assertSame(\lang('Auth.noToken'), $result->reason());
    }

    public function testCheckBadSignatureToken()
    {
        $result = $this->auth->check(['token' => self::BAD_JWT]);

        $this->assertFalse($result->isOK());
        $this->assertSame('Invalid JWT: Signature verification failed', $result->reason());
    }

    public function testCheckNoSubToken()
    {
        $config  = setting('Auth.jwtConfig');
        $payload = [
            'iss' => $config['issuer'],     // issuer
            'aud' => $config['audience'],   // audience
        ];
        $token = FirebaseJWT::encode($payload, $config['secretKey'], $config['algorithm']);

        $result = $this->auth->check(['token' => $token]);

        $this->assertFalse($result->isOK());
        $this->assertSame('Invalid JWT: no user_id', $result->reason());
    }

    public function testCheckOldToken()
    {
        $currentTime = new Time('-1 hour');
        $token       = $this->generateJWT($currentTime);

        $result = $this->auth->check(['token' => $token]);

        $this->assertFalse($result->isOK());
        $this->assertSame('Expired JWT: Expired token', $result->reason());
    }

    public function testCheckNoUserInDatabase()
    {
        $token = $this->generateJWT();

        $users = \model(UserModel::class);
        $users->delete(1);

        $result = $this->auth->check(['token' => $token]);

        $this->assertFalse($result->isOK());
        $this->assertSame(\lang('Auth.invalidUser'), $result->reason());
    }

    public function testCheckSuccess()
    {
        $token = $this->generateJWT();

        $result = $this->auth->check(['token' => $token]);

        $this->assertTrue($result->isOK());
        $this->assertInstanceOf(User::class, $result->extraInfo());
        $this->assertSame(1, $result->extraInfo()->id);
    }

    public function testGetPayload()
    {
        $token = $this->generateJWT();

        $this->auth->check(['token' => $token]);
        $payload = $this->auth->getPayload();

        $this->assertSame((string) $this->user->id, $payload->sub);
        $this->assertSame((\setting('Auth.jwtConfig')['issuer']), $payload->iss);
    }

    public function testAttemptBadSignatureToken()
    {
        $result = $this->auth->attempt([
            'token' => self::BAD_JWT,
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertSame('Invalid JWT: Signature verification failed', $result->reason());

        // A login attempt should have always been recorded
        $this->seeInDatabase('auth_token_logins', [
            'id_type'    => JWT::ID_TYPE_JWT,
            'identifier' => self::BAD_JWT,
            'success'    => 0,
        ]);
    }

    public function testAttemptSuccess()
    {
        $token = $this->generateJWT();

        $result = $this->auth->attempt([
            'token' => $token,
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertSame(1, $foundUser->id);

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_token_logins', [
            'id_type'    => JWT::ID_TYPE_JWT,
            'identifier' => $token,
            'success'    => 1,
        ]);
    }

    public function testRecordActiveDateNoUser()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Authentication\Authenticators\JWT::recordActiveDate() requires logged in user before calling.'
        );

        $this->auth->recordActiveDate();
    }

    /**
     * @param Time|null $currentTime The current time
     */
    private function generateJWT(?Time $currentTime = null): string
    {
        $this->user = \fake(UserModel::class, ['id' => 1, 'username' => 'John Smith']);

        $generator = new JWTGenerator($currentTime);

        return $generator->generateAccessToken($this->user);
    }
}
