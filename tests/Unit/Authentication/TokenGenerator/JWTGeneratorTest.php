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

        // Reset the current time.
        Time::setTestNow();

        $token = $generator->generateAccessToken($user);

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

        // Reset the current time.
        Time::setTestNow();

        $payload = [
            'user_id' => '1',
            'email'   => 'admin@example.jp',
        ];

        $token = $generator->generate($payload, DAY);

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
}
