<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\JWT;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class JWTManagerTest extends TestCase
{
    public function testGenerateAccessToken()
    {
        /** @var User $user */
        $user = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);

        // Fix the current time for testing.
        Time::setTestNow('now');

        $clock     = new Time();
        $generator = new JWTManager($clock);

        $currentTime = $clock->now();

        $token = $generator->generateAccessToken($user);

        // Reset the current time.
        Time::setTestNow();

        $this->assertIsString($token);
        $this->assertStringStartsWith('eyJ', $token);

        return [$token, $currentTime];
    }

    /**
     * @depends testGenerateAccessToken
     */
    public function testGenerateAccessTokenPayload(array $data): void
    {
        [$token, $currentTime] = $data;

        $auth = new JWT(new UserModel());

        $payload = $auth->decodeJWT($token);

        $config   = config(AuthJWT::class);
        $expected = [
            'iss' => $config->defaultClaims['iss'],
            'sub' => '1',
            'iat' => $currentTime->getTimestamp(),
            'exp' => $currentTime->getTimestamp() + $config->timeToLive,
        ];
        $this->assertSame($expected, (array) $payload);
    }

    public function testGenerateAccessTokenAddClaims(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);

        $generator = new JWTManager();

        $claims = [
            'email' => 'admin@example.jp',
        ];
        $token = $generator->generateAccessToken($user, $claims);

        $this->assertIsString($token);

        $payload = $this->decodeJWT($token, 'payload');

        $this->assertStringStartsWith('1', $payload['sub']);
        $this->assertStringStartsWith('admin@example.jp', $payload['email']);
    }

    public function testGenerate()
    {
        // Fix the current time for testing.
        Time::setTestNow('now');

        $clock     = new Time();
        $generator = new JWTManager($clock);

        $currentTime = $clock->now();

        $payload = [
            'user_id' => '1',
            'email'   => 'admin@example.jp',
        ];

        $token = $generator->generate($payload, DAY);

        // Reset the current time.
        Time::setTestNow();

        $this->assertIsString($token);
        $this->assertStringStartsWith('eyJ', $token);

        return [$token, $currentTime];
    }

    /**
     * @depends testGenerate
     */
    public function testGeneratePayload(array $data): void
    {
        [$token, $currentTime] = $data;

        $auth = new JWT(new UserModel());

        $payload = $auth->decodeJWT($token);

        $config   = config(AuthJWT::class);
        $expected = [
            'iss'     => $config->defaultClaims['iss'],
            'user_id' => '1',
            'email'   => 'admin@example.jp',
            'iat'     => $currentTime->getTimestamp(),
            'exp'     => $currentTime->getTimestamp() + DAY,
        ];
        $this->assertSame($expected, (array) $payload);
    }

    public function testGenerateSetKid(): void
    {
        $generator = new JWTManager();

        // Set kid
        $config                            = config(AuthJWT::class);
        $config->keys['default'][0]['kid'] = 'Key01';

        $payload = [
            'user_id' => '1',
        ];
        $token = $generator->generate($payload, DAY);

        $this->assertIsString($token);

        $headers = $this->decodeJWT($token, 'header');
        $this->assertSame([
            'typ' => 'JWT',
            'alg' => 'HS256',
            'kid' => 'Key01',
        ], $headers);
    }

    public function testGenerateAddHeader(): void
    {
        $generator = new JWTManager();

        $payload = [
            'user_id' => '1',
        ];
        $headers = [
            'extra_key' => 'extra_value',
        ];
        $token = $generator->generate($payload, DAY, 'default', $headers);

        $this->assertIsString($token);

        $headers = $this->decodeJWT($token, 'header');
        $this->assertSame([
            'extra_key' => 'extra_value',
            'typ'       => 'JWT',
            'alg'       => 'HS256',
        ], $headers);
    }

    public function testGenerateWithAsymmetricKey(): void
    {
        $generator = new JWTManager();

        $config                     = config(AuthJWT::class);
        $config->keys['default'][0] = [
            'alg'     => 'RS256', // algorithm.
            'public'  => '',      // Public Key
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
        $token = $generator->generate($payload, DAY);

        $this->assertIsString($token);

        $headers = $this->decodeJWT($token, 'header');
        $this->assertSame([
            'typ' => 'JWT',
            'alg' => 'RS256',
        ], $headers);
    }

    private function decodeJWT(string $token, $part): array
    {
        $map = [
            'header'  => 0,
            'payload' => 1,
        ];
        $index = $map[$part];

        return json_decode(
            base64_decode(
                str_replace(
                    '_',
                    '/',
                    str_replace(
                        '-',
                        '+',
                        explode('.', $token)[$index]
                    )
                ),
                true
            ),
            true
        );
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
        $manager = new JWTManager();
        $payload = [
            'user_id' => '1',
        ];
        $token = $manager->generate($payload, DAY, 'default');

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

        $payload = $manager->decode($token);

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
        $manager = new JWTManager();
        $payload = [
            'user_id' => '1',
        ];
        $token = $manager->generate($payload, DAY, 'mobile');

        $payload = $manager->decode($token, 'mobile');

        $this->assertSame('1', $payload->user_id);
    }

    public function testDecodeJWTCanDecodeWithAsymmetricKey(): void
    {
        $token = $this->generateJWTWithAsymmetricKey();

        $manager = new JWTManager();
        $payload = $manager->decode($token);

        $this->assertSame('1', $payload->user_id);
    }

    private function generateJWTWithAsymmetricKey(): string
    {
        $manager = new JWTManager();

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

        return $manager->generate($payload, DAY);
    }
}
