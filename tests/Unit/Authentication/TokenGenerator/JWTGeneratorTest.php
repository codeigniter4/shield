<?php

namespace Tests\Unit\Authentication\TokenGenerator;

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
    public function testGenerate()
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
     * @depends testGenerate
     */
    public function testTokenSubIsUserId(string $token)
    {
        $auth = new JWT(new UserModel());

        $payload = $auth->decodeJWT($token);

        $this->assertSame('1', $payload->sub);
    }
}
