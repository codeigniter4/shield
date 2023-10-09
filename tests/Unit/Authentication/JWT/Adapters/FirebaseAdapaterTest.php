<?php

declare(strict_types=1);

namespace Tests\Unit\Authentication\JWT\Adapters;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\JWT\Adapters\FirebaseAdapter;
use CodeIgniter\Shield\Authentication\JWT\Exceptions\InvalidTokenException;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException as ShieldInvalidArgumentException;
use CodeIgniter\Shield\Exceptions\LogicException as ShieldLogicException;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\TestCase;
use UnexpectedValueException;

/**
 * @internal
 */
final class FirebaseAdapaterTest extends TestCase
{
    public function testDecode(): void
    {
        $token = $this->generateJWT();

        $adapter = new FirebaseAdapter();

        /** @var AuthJWT $config */
        $config = config('AuthJWT');

        $key     = 'default';
        $payload = $adapter->decode($token, $key);

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

        return $generator->generateToken($user);
    }

    public function testDecodeSignatureInvalidException(): void
    {
        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage(lang('Auth.invalidJWT'));

        $adapter = new FirebaseAdapter();

        $key   = 'default';
        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJJc3N1ZXIgb2YgdGhlIEpXVCIsImF1ZCI6IkF1ZGllbmNlIG9mIHRoZSBKV1QiLCJzdWIiOiIxIiwiaWF0IjoxNjUzOTkxOTg5LCJleHAiOjE2NTM5OTU1ODl9.hgOYHEcT6RGHb3po1lspTcmjrylY1Cy1IvYmHOyx0CY';
        $adapter->decode($token, $key);
    }

    public function testDecodeExpiredToken(): void
    {
        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage(lang('Auth.expiredJWT'));

        $adapter = new FirebaseAdapter();

        Time::setTestNow('-1 hour');
        $token = $this->generateJWT();
        Time::setTestNow();

        $key = 'default';
        $adapter->decode($token, $key);
    }

    public function testDecodeInvalidTokenExceptionUnexpectedValueException(): void
    {
        $token = $this->generateJWT();

        // Change algorithm and it makes the key invalid.
        /** @var AuthJWT $config */
        $config                            = config('AuthJWT');
        $config->keys['default'][0]['alg'] = 'ES256';

        $adapter = new FirebaseAdapter();

        try {
            $key = 'default';
            $adapter->decode($token, $key);
        } catch (InvalidTokenException $e) {
            $prevException = $e->getPrevious();

            $this->assertInstanceOf(UnexpectedValueException::class, $prevException);
            $this->assertSame('Incorrect key for this algorithm', $prevException->getMessage());

            return;
        }

        $this->fail('InvalidTokenException is not thrown.');
    }

    public function testDecodeInvalidArgumentException(): void
    {
        $this->expectException(ShieldInvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Keyset: "default". Key material must not be empty');

        $token = $this->generateJWT();

        // Set invalid key.
        /** @var AuthJWT $config */
        $config                     = config('AuthJWT');
        $config->keys['default'][0] = [
            'alg'    => '',
            'secret' => '',
        ];

        $adapter = new FirebaseAdapter();

        $key = 'default';
        $adapter->decode($token, $key);
    }

    public function testEncodeLogicExceptionLogicException(): void
    {
        $this->expectException(ShieldLogicException::class);
        $this->expectExceptionMessage('Cannot encode JWT: Algorithm not supported');

        // Set unsupported algorithm.
        /** @var AuthJWT $config */
        $config                            = config('AuthJWT');
        $config->keys['default'][0]['alg'] = 'PS256';

        $adapter = new FirebaseAdapter();

        $key     = 'default';
        $payload = [
            'iss' => 'http://example.org',
            'aud' => 'http://example.com',
            'iat' => 1_356_999_524,
            'nbf' => 1_357_000_000,
        ];
        $adapter->encode($payload, $key);
    }
}
