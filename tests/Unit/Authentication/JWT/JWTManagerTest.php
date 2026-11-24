<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Tests\Unit\Authentication\JWT;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\JWT\Exceptions\InvalidTokenException;
use CodeIgniter\Shield\Authentication\JWT\JWSDecoder;
use CodeIgniter\Shield\Authentication\JWT\JWSEncoder;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\DatabaseException;
use CodeIgniter\Shield\Models\JWTRevocationModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;

/**
 * @internal
 */
final class JWTManagerTest extends TestCase
{
    private function createJWTManager(?Time $clock = null): JWTManager
    {
        $store = $this->createStub(JWTRevocationModel::class);
        $store->method('isRevoked')->willReturn(false);

        return new JWTManager($clock, revocationModel: $store);
    }

    public function testGenerateToken(): array
    {
        $user = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);

        // Fix the current time for testing.
        Time::setTestNow('now');

        $clock   = new Time();
        $manager = $this->createJWTManager($clock);

        $currentTime = $clock::now();

        $token = $manager->generateToken($user);

        // Reset the current time.
        Time::setTestNow();

        $this->assertStringStartsWith('eyJ', $token);

        return [$token, $currentTime];
    }

    #[Depends('testGenerateToken')]
    public function testGenerateTokenPayload(array $data): void
    {
        [$token, $currentTime] = $data;

        $manager = $this->createJWTManager();
        $payload = $manager->parse($token);

        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\z/', $payload->jti);

        /** @var AuthJWT $config */
        $config   = config('AuthJWT');
        $expected = [
            'iss' => $config->defaultClaims['iss'],
            'sub' => '1',
            'jti' => $payload->jti,
            'iat' => $currentTime->getTimestamp(),
            'exp' => $currentTime->getTimestamp() + $config->timeToLive,
        ];
        $this->assertSame($expected, (array) $payload);
    }

    public function testGenerateTokenAddClaims(): void
    {
        $user = fake(UserModel::class, ['id' => 1, 'username' => 'John Smith'], false);

        $manager = $this->createJWTManager();

        $claims = [
            'email' => 'admin@example.jp',
        ];
        $token = $manager->generateToken($user, $claims);

        $payload = $this->decodeJWT($token, 'payload');

        $this->assertStringStartsWith('1', $payload['sub']);
        $this->assertStringStartsWith('admin@example.jp', $payload['email']);
    }

    public function testIssue(): array
    {
        // Fix the current time for testing.
        Time::setTestNow('now');

        $clock   = new Time();
        $manager = $this->createJWTManager($clock);

        $currentTime = $clock::now();

        $payload = [
            'user_id' => '1',
            'email'   => 'admin@example.jp',
        ];

        $token = $manager->issue($payload, DAY);

        // Reset the current time.
        Time::setTestNow();

        $this->assertStringStartsWith('eyJ', $token);

        return [$token, $currentTime];
    }

    #[Depends('testIssue')]
    public function testIssuePayload(array $data): void
    {
        [$token, $currentTime] = $data;

        $manager = $this->createJWTManager();
        $payload = $manager->parse($token);

        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\z/', $payload->jti);

        /** @var AuthJWT $config */
        $config   = config('AuthJWT');
        $expected = [
            'iss'     => $config->defaultClaims['iss'],
            'user_id' => '1',
            'email'   => 'admin@example.jp',
            'jti'     => $payload->jti,
            'iat'     => $currentTime->getTimestamp(),
            'exp'     => $currentTime->getTimestamp() + DAY,
        ];
        $this->assertSame($expected, (array) $payload);
    }

    public function testIssueSetKid(): void
    {
        $manager = $this->createJWTManager();

        // Set kid
        /** @var AuthJWT $config */
        $config                            = config('AuthJWT');
        $config->keys['default'][0]['kid'] = 'Key01';

        $payload = [
            'user_id' => '1',
        ];
        $token = $manager->issue($payload, DAY);

        $headers = $this->decodeJWT($token, 'header');
        $this->assertSame([
            'typ' => 'JWT',
            'alg' => 'HS256',
            'kid' => 'Key01',
        ], $headers);
    }

    public function testIssueAddHeader(): void
    {
        $manager = $this->createJWTManager();

        $payload = [
            'user_id' => '1',
        ];
        $headers = [
            'extra_key' => 'extra_value',
        ];
        $token = $manager->issue($payload, DAY, 'default', $headers);

        $headers = $this->decodeJWT($token, 'header');
        $this->assertEqualsCanonicalizing([
            'typ'       => 'JWT',
            'extra_key' => 'extra_value',
            'alg'       => 'HS256',
        ], $headers);
    }

    public function testIssueWithAsymmetricKey(): void
    {
        $manager = $this->createJWTManager();

        /** @var AuthJWT $config */
        $config                     = config('AuthJWT');
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
        $token = $manager->issue($payload, DAY);

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
                        explode('.', $token)[$index],
                    ),
                ),
                true,
            ),
            true,
        );
    }

    public function testParseCanDecodeTokenSignedByOldKey(): void
    {
        /** @var AuthJWT $config */
        $config                  = config('AuthJWT');
        $config->keys['default'] = [
            [
                'kid'    => 'Key01',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key01_Secret_at_least_256_bits!!',
            ],
        ];

        // Generate token with Key01.
        $manager = $this->createJWTManager();
        $payload = [
            'user_id' => '1',
        ];
        $token = $manager->issue($payload, DAY, 'default');

        // Add new Key02.
        $config->keys['default'] = [
            [
                'kid'    => 'Key02',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key02_Secret_at_least_256_bits!!',
            ],
            [
                'kid'    => 'Key01',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key01_Secret_at_least_256_bits!!',
            ],
        ];

        $payload = $manager->parse($token);

        $this->assertSame('1', $payload->user_id);
    }

    public function testParseCanSpecifyKey(): void
    {
        /** @var AuthJWT $config */
        $config                 = config('AuthJWT');
        $config->keys['mobile'] = [
            [
                'kid'    => 'Key01',
                'alg'    => 'HS256', // algorithm.
                'secret' => 'Key01_Secret_at_least_256_bits!!',
            ],
        ];

        // Generate token with the mobile key.
        $manager = $this->createJWTManager();
        $payload = [
            'user_id' => '1',
        ];
        $token = $manager->issue($payload, DAY, 'mobile');

        $payload = $manager->parse($token, 'mobile');

        $this->assertSame('1', $payload->user_id);
    }

    public function testParseCanDecodeWithAsymmetricKey(): void
    {
        $token = $this->generateJWTWithAsymmetricKey();

        $manager = $this->createJWTManager();
        $payload = $manager->parse($token);

        $this->assertSame('1', $payload->user_id);
    }

    public function testLegacyTokenWithoutIdentifierParsesWithoutStorageAndCannotBeRevoked(): void
    {
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->never())->method('isRevoked');
        $store->expects($this->never())->method('revoke');
        $manager = new JWTManager(revocationModel: $store);

        // Before JWTManager automatically added an identifier, it delegated directly to the encoder.
        $token   = (new JWSEncoder())->encode(['sub' => '42']);
        $payload = $manager->parse($token);

        $this->assertObjectNotHasProperty('jti', $payload);
        $this->assertSame('42', $payload->sub);
        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage(lang('Auth.invalidJWT'));

        $manager->revokeToken($token);
    }

    public function testIssuanceGeneratesDistinctIdentifiersAndPreservesExplicitValues(): void
    {
        $manager = $this->createJWTManager();
        $user    = fake(UserModel::class, ['id' => 42], false);

        $first  = $this->decodeJWT($manager->issue(['sub' => '42']), 'payload');
        $second = $this->decodeJWT($manager->generateToken($user), 'payload');

        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\z/', $first['jti']);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\z/', $second['jti']);
        $this->assertNotSame($first['jti'], $second['jti']);
        $this->assertSame('explicit', $this->decodeJWT($manager->issue(['jti' => 'explicit']), 'payload')['jti']);
        $this->assertNull($this->decodeJWT($manager->issue(['jti' => null]), 'payload')['jti']);
    }

    public function testRevocationUsesOnlyVerifiedIssuerAndIdentifier(): void
    {
        $store     = $this->createMock(JWTRevocationModel::class);
        $manager   = new JWTManager(revocationModel: $store);
        $expiresAt = Time::now()->getTimestamp() + MINUTE;
        $token     = $manager->issue(['iss' => 'issuer', 'jti' => 'identifier', 'exp' => $expiresAt]);

        $store->expects($this->exactly(2))->method('revoke')
            ->with(hash('sha256', '6:issueridentifier'), $expiresAt);
        $store->expects($this->never())->method('isRevoked');

        $manager->revokeToken($token);
        $manager->revokeToken($token);
    }

    public function testRevocationDoesNotWriteForAnInvalidToken(): void
    {
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->never())->method('revoke');
        $manager = new JWTManager(revocationModel: $store);

        $this->expectException(InvalidTokenException::class);

        $manager->revokeToken('invalid-token');
    }

    public function testParseRejectsRevokedTokenAndAllowsSeparateIssuer(): void
    {
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->exactly(2))->method('isRevoked')
            ->willReturnMap([
                [hash('sha256', '1:abc'), false],
                [hash('sha256', '2:abc'), true],
            ]);
        $manager = new JWTManager(revocationModel: $store);

        $this->assertSame('a', $manager->parse($manager->issue(['iss' => 'a', 'jti' => 'bc']))->iss);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage(lang('Auth.invalidJWT'));

        $manager->parse($manager->issue(['iss' => 'ab', 'jti' => 'c']));
    }

    /**
     * @param array<string, mixed> $claims
     */
    #[DataProvider('provideParseRejectsInvalidRevocationClaimsBeforeCheckingStorage')]
    public function testParseRejectsInvalidRevocationClaimsBeforeCheckingStorage(array $claims): void
    {
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->never())->method('isRevoked');
        $decoder = $this->createMock(JWSDecoder::class);
        $decoder->expects($this->once())->method('decode')->willReturn((object) $claims);
        $manager = new JWTManager(jwsDecoder: $decoder, revocationModel: $store);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage(lang('Auth.invalidJWT'));

        $manager->parse('verified-by-mock');
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function provideParseRejectsInvalidRevocationClaimsBeforeCheckingStorage(): iterable
    {
        $claims = ['iss' => 'issuer', 'jti' => 'identifier', 'exp' => time() + DAY];

        yield 'explicit null jti' => [['jti' => null] + $claims];

        yield 'empty jti' => [['jti' => ''] + $claims];

        yield 'numeric jti' => [['jti' => 42] + $claims];

        yield 'long jti' => [['jti' => str_repeat('a', 256)] + $claims];

        yield 'empty issuer' => [['iss' => ''] + $claims];

        yield 'invalid issuer' => [['iss' => ['issuer']] + $claims];

        yield 'issuer missing' => [array_diff_key($claims, ['iss' => true])];

        yield 'expiry missing' => [array_diff_key($claims, ['exp' => true])];

        yield 'expiry not integer' => [['exp' => (string) $claims['exp']] + $claims];
    }

    public function testManagedExpiryIsStrictEvenWhenDecoderAcceptsTheToken(): void
    {
        $clock = Time::parse('2026-06-13 12:00:00', 'UTC');
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->never())->method('isRevoked');
        $decoder = $this->createMock(JWSDecoder::class);
        $decoder->expects($this->once())->method('decode')->willReturn((object) [
            'iss' => 'issuer',
            'jti' => 'identifier',
            'exp' => $clock->getTimestamp(),
        ]);
        $manager = new JWTManager($clock, jwsDecoder: $decoder, revocationModel: $store);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage(lang('Auth.expiredJWT'));

        Time::setTestNow($clock);

        try {
            $manager->parse('verified-by-mock');
        } finally {
            Time::setTestNow();
        }
    }

    public function testRevocationStoreReadFailureDoesNotAuthenticate(): void
    {
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->once())->method('isRevoked')
            ->willThrowException(new DatabaseException('private database detail'));
        $manager = new JWTManager(revocationModel: $store);

        try {
            $manager->parse($manager->issue(['sub' => '42']));
            $this->fail('Revocation store failure allowed the token.');
        } catch (InvalidTokenException $exception) {
            $this->assertSame(lang('Auth.invalidJWT'), $exception->getMessage());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function testExpiryIsRecheckedWhenARevocationRecordCanBePurgedDuringLookup(): void
    {
        $clock  = Time::parse('2026-06-13 12:00:00', 'UTC');
        $expiry = $clock->addSeconds(1);
        $store  = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->once())->method('isRevoked')->willReturnCallback(static function () use ($expiry): bool {
            Time::setTestNow($expiry);

            return false;
        });
        $decoder = $this->createMock(JWSDecoder::class);
        $decoder->expects($this->once())->method('decode')->willReturn((object) [
            'iss' => 'issuer',
            'jti' => 'identifier',
            'exp' => $expiry->getTimestamp(),
        ]);
        $manager = new JWTManager($clock, jwsDecoder: $decoder, revocationModel: $store);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage(lang('Auth.expiredJWT'));

        Time::setTestNow($clock);

        try {
            $manager->parse('verified-by-mock');
        } finally {
            Time::setTestNow();
        }
    }

    public function testRevocationStoreWriteFailureIsSanitized(): void
    {
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->once())->method('revoke')
            ->willThrowException(new DatabaseException('private database detail'));
        $manager = new JWTManager(revocationModel: $store);

        try {
            $manager->revokeToken($manager->issue(['sub' => '42']));
            $this->fail('A failed write was reported as successful.');
        } catch (RuntimeException $exception) {
            $this->assertSame('JWT revocation could not be stored.', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function testPurgeUsesTheCurrentTimeAndReturnsTheRemovedCount(): void
    {
        $clock = Time::parse('2026-06-13 12:00:00', 'UTC');
        $store = $this->createMock(JWTRevocationModel::class);
        $store->expects($this->once())->method('purgeExpired')->with($clock->getTimestamp())->willReturn(2);
        $manager = new JWTManager($clock, revocationModel: $store);

        Time::setTestNow($clock);

        try {
            $this->assertSame(2, $manager->purgeExpiredRevocations());
        } finally {
            Time::setTestNow();
        }
    }

    private function generateJWTWithAsymmetricKey(): string
    {
        $manager = $this->createJWTManager();

        /** @var AuthJWT $config */
        $config                     = config('AuthJWT');
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

        return $manager->issue($payload, DAY);
    }
}
