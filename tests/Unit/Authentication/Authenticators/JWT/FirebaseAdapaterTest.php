<?php

namespace Tests\Unit\Authentication\Authenticators\JWT;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\JWT\FirebaseAdapter;
use CodeIgniter\Shield\Authentication\TokenGenerator\JWTGenerator;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class FirebaseAdapaterTest extends TestCase
{
    public function testDecode()
    {
        $token = $this->generateJWT();

        $jwtDecoder = new FirebaseAdapter();

        $config    = setting('Auth.jwtConfig');
        $key       = $config['secretKey'];
        $algorithm = $config['algorithm'];

        $payload = $jwtDecoder->decode($token, $key, $algorithm);

        $this->assertSame(setting('Auth.jwtConfig')['claims']['iss'], $payload->iss);
        $this->assertSame(setting('Auth.jwtConfig')['claims']['aud'], $payload->aud);
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

    public function testDecodeSignatureInvalidException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT: Signature verification failed');

        $jwtDecoder = new FirebaseAdapter();

        $config    = setting('Auth.jwtConfig');
        $key       = $config['secretKey'];
        $algorithm = $config['algorithm'];

        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJJc3N1ZXIgb2YgdGhlIEpXVCIsImF1ZCI6IkF1ZGllbmNlIG9mIHRoZSBKV1QiLCJzdWIiOiIxIiwiaWF0IjoxNjUzOTkxOTg5LCJleHAiOjE2NTM5OTU1ODl9.hgOYHEcT6RGHb3po1lspTcmjrylY1Cy1IvYmHOyx0CY';
        $jwtDecoder->decode($token, $key, $algorithm);
    }

    public function testDecodeExpiredException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expired JWT: Expired token');

        $jwtDecoder = new FirebaseAdapter();

        $currentTime = new Time('-1 hour');
        $token       = $this->generateJWT($currentTime);

        $config    = setting('Auth.jwtConfig');
        $key       = $config['secretKey'];
        $algorithm = $config['algorithm'];

        $jwtDecoder->decode($token, $key, $algorithm);
    }
}
