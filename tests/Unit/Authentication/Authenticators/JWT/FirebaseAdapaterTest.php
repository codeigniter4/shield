<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\Authenticators\JWT;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\JWT\FirebaseAdapter;
use CodeIgniter\Shield\Authentication\TokenGenerator\JWTGenerator;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class FirebaseAdapaterTest extends TestCase
{
    public function testDecode(): void
    {
        $token = $this->generateJWT();

        $jwtDecoder = new FirebaseAdapter();

        /** @var AuthJWT $config */
        $config = config('AuthJWT');

        $key       = $config->secretKey;
        $algorithm = $config->algorithm;

        $payload = $jwtDecoder->decode($token, $key, $algorithm);

        $this->assertSame($config->claims['iss'], $payload->iss);
        $this->assertSame('1', $payload->sub);
    }

    /**
     * @param Time|null $currentTime The current time
     */
    public static function generateJWT(?Time $currentTime = null): string
    {
        /** @var User $user */
        $user = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);

        $generator = new JWTGenerator($currentTime);

        return $generator->generateAccessToken($user);
    }

    public function testDecodeSignatureInvalidException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT: Signature verification failed');

        $jwtDecoder = new FirebaseAdapter();

        /** @var AuthJWT $config */
        $config    = config('AuthJWT');
        $key       = $config->secretKey;
        $algorithm = $config->algorithm;

        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJJc3N1ZXIgb2YgdGhlIEpXVCIsImF1ZCI6IkF1ZGllbmNlIG9mIHRoZSBKV1QiLCJzdWIiOiIxIiwiaWF0IjoxNjUzOTkxOTg5LCJleHAiOjE2NTM5OTU1ODl9.hgOYHEcT6RGHb3po1lspTcmjrylY1Cy1IvYmHOyx0CY';
        $jwtDecoder->decode($token, $key, $algorithm);
    }

    public function testDecodeExpiredException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expired JWT: Expired token');

        $jwtDecoder = new FirebaseAdapter();

        $currentTime = new Time('-1 hour');
        $token       = $this->generateJWT($currentTime);

        /** @var AuthJWT $config */
        $config    = config('AuthJWT');
        $key       = $config->secretKey;
        $algorithm = $config->algorithm;

        $jwtDecoder->decode($token, $key, $algorithm);
    }
}
