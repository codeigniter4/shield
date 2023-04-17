<?php

declare(strict_types=1);

namespace Tests\Authentication\Authenticators;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use CodeIgniter\Shield\Authentication\JWT\JWTGenerator;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Config\AuthJWT;
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
        $auth->setProvider(\model(UserModel::class));

        /** @var JWT $authenticator */
        $authenticator = $auth->factory('jwt');
        $this->auth    = $authenticator;

        Services::injectMock('events', new MockEvents());
    }

    private function createUser(): User
    {
        return \fake(UserModel::class);
    }

    public function testLogin(): void
    {
        $user = $this->createUser();

        $this->auth->login($user);

        // Stores the user
        $this->assertInstanceOf(User::class, $this->auth->getUser());
        $this->assertSame($user->id, $this->auth->getUser()->id);
    }

    public function testLogout(): void
    {
        // this one's a little odd since it's stateless, but roll with it...

        $user = $this->createUser();

        $this->auth->login($user);
        $this->assertNotNull($this->auth->getUser());

        $this->auth->logout();
        $this->assertNull($this->auth->getUser());
    }

    public function testLoginById(): void
    {
        $user = $this->createUser();

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
    }

    public function testLoginByIdNoUser(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unable to locate the specified user.');

        $this->createUser();

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById(9999);
    }

    public function testCheckNoToken(): void
    {
        $result = $this->auth->check([]);

        $this->assertFalse($result->isOK());
        $this->assertSame(\lang('Auth.noToken'), $result->reason());
    }

    public function testCheckBadSignatureToken(): void
    {
        $result = $this->auth->check(['token' => self::BAD_JWT]);

        $this->assertFalse($result->isOK());
        $this->assertSame('Invalid JWT: Signature verification failed', $result->reason());
    }

    public function testCheckNoSubToken(): void
    {
        /** @var AuthJWT $config */
        $config  = config('AuthJWT');
        $payload = [
            'iss' => $config->defaultClaims['iss'], // issuer
        ];
        $token = FirebaseJWT::encode($payload, $config->keys['default'][0]['secret'], $config->keys['default'][0]['alg']);

        $result = $this->auth->check(['token' => $token]);

        $this->assertFalse($result->isOK());
        $this->assertSame('Invalid JWT: no user_id', $result->reason());
    }

    public function testCheckOldToken(): void
    {
        Time::setTestNow('-1 hour');
        $token = $this->generateJWT();
        Time::setTestNow();

        $result = $this->auth->check(['token' => $token]);

        $this->assertFalse($result->isOK());
        $this->assertSame('Expired JWT: Expired token', $result->reason());
    }

    public function testCheckNoUserInDatabase(): void
    {
        $token = $this->generateJWT();

        $users = \model(UserModel::class);
        $users->delete(1);

        $result = $this->auth->check(['token' => $token]);

        $this->assertFalse($result->isOK());
        $this->assertSame(\lang('Auth.invalidUser'), $result->reason());
    }

    public function testCheckSuccess(): void
    {
        $token = $this->generateJWT();

        $result = $this->auth->check(['token' => $token]);

        $this->assertTrue($result->isOK());
        $this->assertInstanceOf(User::class, $result->extraInfo());
        $this->assertSame(1, $result->extraInfo()->id);
    }

    public function testGetPayload(): void
    {
        $token = $this->generateJWT();

        $this->auth->check(['token' => $token]);
        $payload = $this->auth->getPayload();

        $this->assertSame((string) $this->user->id, $payload->sub);
        /** @var AuthJWT $config */
        $config = config('AuthJWT');
        $this->assertSame($config->defaultClaims['iss'], $payload->iss);
    }

    public function testAttemptBadSignatureToken(): void
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

    public function testAttemptSuccess(): void
    {
        // Change $recordLoginAttempt in Config.
        /** @var AuthJWT $config */
        $config                     = config('AuthJWT');
        $config->recordLoginAttempt = Auth::RECORD_LOGIN_ATTEMPT_ALL;

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

    public function testRecordActiveDateNoUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Authentication\Authenticators\JWT::recordActiveDate() requires logged in user before calling.'
        );

        $this->auth->recordActiveDate();
    }

    /**
     * @param Time|null $clock The Time object
     */
    private function generateJWT(?Time $clock = null): string
    {
        $this->user = \fake(UserModel::class, ['id' => 1, 'username' => 'John Smith']);

        $generator = new JWTGenerator($clock);

        return $generator->generateAccessToken($this->user);
    }

    public function testDecodeJWTCanDecodeTokenSignedByOldKey(): void
    {
        $config                  = config(AuthJWT::class);
        $config->keys['default'] = [
            [
                'kid'    => 'Key01',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key01_Secret',
            ],
        ];

        // Generate token with Key01.
        $generator = new JWTGenerator();
        $payload   = [
            'user_id' => '1',
        ];
        $token = $generator->generate($payload, DAY, 'default');

        // Add new Key02.
        $config->keys['default'] = [
            [
                'kid'    => 'Key02',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key02_Secret',
            ],
            [
                'kid'    => 'Key01',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key01_Secret',
            ],
        ];

        $payload = $this->auth->decodeJWT($token);

        $this->assertSame('1', $payload->user_id);
    }

    public function testDecodeJWTCanSpecifyKey(): void
    {
        $config                 = config(AuthJWT::class);
        $config->keys['mobile'] = [
            [
                'kid'    => 'Key01',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key01_Secret',
            ],
        ];

        // Generate token with the mobile key.
        $generator = new JWTGenerator();
        $payload   = [
            'user_id' => '1',
        ];
        $token = $generator->generate($payload, DAY, 'mobile');

        $this->auth->setKeyset('mobile');
        $payload = $this->auth->decodeJWT($token);

        $this->assertSame('1', $payload->user_id);
    }

    public function testDecodeJWTCanDecodeWithAsymmetricKey(): void
    {
        $token = $this->generateJWTWithAsymmetricKey();

        $payload = $this->auth->decodeJWT($token);

        $this->assertSame('1', $payload->user_id);
    }

    private function generateJWTWithAsymmetricKey(): string
    {
        $generator = new JWTGenerator();

        $config                     = config(AuthJWT::class);
        $config->keys['default'][0] = [
            'alg'    => 'RS256', // algorithm.
            'public' => <<<'EOD'
                -----BEGIN PUBLIC KEY-----
                MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuzWHNM5f+amCjQztc5QT
                fJfzCC5J4nuW+L/aOxZ4f8J3FrewM2c/dufrnmedsApb0By7WhaHlcqCh/ScAPyJ
                hzkPYLae7bTVro3hok0zDITR8F6SJGL42JAEUk+ILkPI+DONM0+3vzk6Kvfe548t
                u4czCuqU8BGVOlnp6IqBHhAswNMM78pos/2z0CjPM4tbeXqSTTbNkXRboxjU29vS
                opcT51koWOgiTf3C7nJUoMWZHZI5HqnIhPAG9yv8HAgNk6CMk2CadVHDo4IxjxTz
                TTqo1SCSH2pooJl9O8at6kkRYsrZWwsKlOFE2LUce7ObnXsYihStBUDoeBQlGG/B
                wQIDAQAB
                -----END PUBLIC KEY-----
                EOD,
            'private' => <<<'EOD'
                -----BEGIN RSA PRIVATE KEY-----
                MIIEowIBAAKCAQEAuzWHNM5f+amCjQztc5QTfJfzCC5J4nuW+L/aOxZ4f8J3Frew
                M2c/dufrnmedsApb0By7WhaHlcqCh/ScAPyJhzkPYLae7bTVro3hok0zDITR8F6S
                JGL42JAEUk+ILkPI+DONM0+3vzk6Kvfe548tu4czCuqU8BGVOlnp6IqBHhAswNMM
                78pos/2z0CjPM4tbeXqSTTbNkXRboxjU29vSopcT51koWOgiTf3C7nJUoMWZHZI5
                HqnIhPAG9yv8HAgNk6CMk2CadVHDo4IxjxTzTTqo1SCSH2pooJl9O8at6kkRYsrZ
                WwsKlOFE2LUce7ObnXsYihStBUDoeBQlGG/BwQIDAQABAoIBAFtGaOqNKGwggn9k
                6yzr6GhZ6Wt2rh1Xpq8XUz514UBhPxD7dFRLpbzCrLVpzY80LbmVGJ9+1pJozyWc
                VKeCeUdNwbqkr240Oe7GTFmGjDoxU+5/HX/SJYPpC8JZ9oqgEA87iz+WQX9hVoP2
                oF6EB4ckDvXmk8FMwVZW2l2/kd5mrEVbDaXKxhvUDf52iVD+sGIlTif7mBgR99/b
                c3qiCnxCMmfYUnT2eh7Vv2LhCR/G9S6C3R4lA71rEyiU3KgsGfg0d82/XWXbegJW
                h3QbWNtQLxTuIvLq5aAryV3PfaHlPgdgK0ft6ocU2de2FagFka3nfVEyC7IUsNTK
                bq6nhAECgYEA7d/0DPOIaItl/8BWKyCuAHMss47j0wlGbBSHdJIiS55akMvnAG0M
                39y22Qqfzh1at9kBFeYeFIIU82ZLF3xOcE3z6pJZ4Dyvx4BYdXH77odo9uVK9s1l
                3T3BlMcqd1hvZLMS7dviyH79jZo4CXSHiKzc7pQ2YfK5eKxKqONeXuECgYEAyXlG
                vonaus/YTb1IBei9HwaccnQ/1HRn6MvfDjb7JJDIBhNClGPt6xRlzBbSZ73c2QEC
                6Fu9h36K/HZ2qcLd2bXiNyhIV7b6tVKk+0Psoj0dL9EbhsD1OsmE1nTPyAc9XZbb
                OPYxy+dpBCUA8/1U9+uiFoCa7mIbWcSQ+39gHuECgYAz82pQfct30aH4JiBrkNqP
                nJfRq05UY70uk5k1u0ikLTRoVS/hJu/d4E1Kv4hBMqYCavFSwAwnvHUo51lVCr/y
                xQOVYlsgnwBg2MX4+GjmIkqpSVCC8D7j/73MaWb746OIYZervQ8dbKahi2HbpsiG
                8AHcVSA/agxZr38qvWV54QKBgCD5TlDE8x18AuTGQ9FjxAAd7uD0kbXNz2vUYg9L
                hFL5tyL3aAAtUrUUw4xhd9IuysRhW/53dU+FsG2dXdJu6CxHjlyEpUJl2iZu/j15
                YnMzGWHIEX8+eWRDsw/+Ujtko/B7TinGcWPz3cYl4EAOiCeDUyXnqnO1btCEUU44
                DJ1BAoGBAJuPD27ErTSVtId90+M4zFPNibFP50KprVdc8CR37BE7r8vuGgNYXmnI
                RLnGP9p3pVgFCktORuYS2J/6t84I3+A17nEoB4xvhTLeAinAW/uTQOUmNicOP4Ek
                2MsLL2kHgL8bLTmvXV4FX+PXphrDKg1XxzOYn0otuoqdAQrkK4og
                -----END RSA PRIVATE KEY-----
                EOD,
        ];

        $payload = [
            'user_id' => '1',
        ];

        return $generator->generate($payload, DAY);
    }
}
