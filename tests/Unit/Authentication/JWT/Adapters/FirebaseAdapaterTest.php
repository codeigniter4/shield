<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\JWT\Adapters;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\JWT\Adapters\FirebaseAdapter;
use CodeIgniter\Shield\Authentication\JWTManager;
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

        $key = 'default';

        $payload = $jwtDecoder->decode($token, $key);

        $this->assertSame($config->defaultClaims['iss'], $payload->iss);
        $this->assertSame('1', $payload->sub);
    }

    /**
     * @param Time|null $clock The Time object
     */
    public static function generateJWT(?Time $clock = null): string
    {
        /** @var User $user */
        $user = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);

        $generator = new JWTManager($clock);

        return $generator->generateAccessToken($user);
    }

    public function testDecodeSignatureInvalidException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT: Signature verification failed');

        $jwtDecoder = new FirebaseAdapter();

        $key   = 'default';
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJJc3N1ZXIgb2YgdGhlIEpXVCIsImF1ZCI6IkF1ZGllbmNlIG9mIHRoZSBKV1QiLCJzdWIiOiIxIiwiaWF0IjoxNjUzOTkxOTg5LCJleHAiOjE2NTM5OTU1ODl9.hgOYHEcT6RGHb3po1lspTcmjrylY1Cy1IvYmHOyx0CY';
        $jwtDecoder->decode($token, $key);
    }

    public function testDecodeExpiredException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expired JWT: Expired token');

        $jwtDecoder = new FirebaseAdapter();

        Time::setTestNow('-1 hour');
        $token = $this->generateJWT();
        Time::setTestNow();

        $key = 'default';
        $jwtDecoder->decode($token, $key);
    }
}
