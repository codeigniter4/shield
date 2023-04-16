<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\TokenGenerator;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use CodeIgniter\Shield\Authentication\TokenGenerator\JWTGenerator;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class JWTGeneratorTest extends TestCase
{
    public function testGenerateAccessToken()
    {
        /** @var User $user */
        $user = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);

        // Fix the current time for testing.
        Time::setTestNow('now');

        $clock     = new Time();
        $generator = new JWTGenerator($clock);

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

    public function testGenerate()
    {
        // Fix the current time for testing.
        Time::setTestNow('now');

        $clock     = new Time();
        $generator = new JWTGenerator($clock);

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
        $generator = new JWTGenerator();

        // Set kid
        $config                            = config(AuthJWT::class);
        $config->keys['default'][0]['kid'] = 'Key01';

        $payload = [
            'user_id' => '1',
        ];
        $token = $generator->generate($payload, DAY);

        $this->assertIsString($token);

        $headers = $this->decodeJWTHeader($token);
        $this->assertSame([
            'typ' => 'JWT',
            'alg' => 'HS256',
            'kid' => 'Key01',
        ], $headers);
    }

    public function testGenerateAddHeader(): void
    {
        $generator = new JWTGenerator();

        $payload = [
            'user_id' => '1',
        ];
        $headers = [
            'extra_key' => 'extra_value',
        ];
        $token = $generator->generate($payload, DAY, 'default', $headers);

        $this->assertIsString($token);

        $headers = $this->decodeJWTHeader($token);
        $this->assertSame([
            'extra_key' => 'extra_value',
            'typ'       => 'JWT',
            'alg'       => 'HS256',
        ], $headers);
    }

    private function decodeJWTHeader(string $token): array
    {
        return json_decode(
            base64_decode(
                str_replace(
                    '_',
                    '/',
                    str_replace(
                        '-',
                        '+',
                        explode('.', $token)[0]
                    )
                ),
                true
            ),
            true
        );
    }
}
