<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\TokenGenerator;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use CodeIgniter\Shield\Authentication\TokenGenerator\JWTGenerator;
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
        $user      = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);
        $generator = new JWTGenerator();

        $token = $generator->generateAccessToken($user);

        $this->assertIsString($token);
        $this->assertStringStartsWith('eyJ', $token);

        return $token;
    }

    /**
     * @depends testGenerateAccessToken
     */
    public function testTokenSubIsUserId(string $token): void
    {
        $auth = new JWT(new UserModel());

        $payload = $auth->decodeJWT($token);

        $this->assertSame('1', $payload->sub);
    }

    public function testGenerate()
    {
        $currentTime = new Time();
        $generator   = new JWTGenerator($currentTime);

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
    public function testTokenHasIatAndExp(array $data): void
    {
        [$token, $currentTime] = $data;

        $auth = new JWT(new UserModel());

        $payload = $auth->decodeJWT($token);

        $expected = [
            'user_id' => '1',
            'email'   => 'admin@example.jp',
            'iat'     => $currentTime->getTimestamp(),
            'exp'     => $currentTime->getTimestamp() + DAY,
        ];
        $this->assertSame($expected, (array) $payload);
    }
}
