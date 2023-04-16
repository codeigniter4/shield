<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\Authenticators\JWT;

use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

class FirebaseAdapter implements JWTAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public static function decode(string $encodedToken, $key): stdClass
    {
        $keyGroup = $key;

        $config = config(AuthJWT::class);

        $configKeys = $config->keys[$keyGroup];

        if (count($configKeys) === 1) {
            $key       = $configKeys[0]['secret'] ?? $configKeys[0]['public'];
            $algorithm = $configKeys[0]['alg'];

            $keys = new Key($key, $algorithm);
        } else {
            $keys = [];

            foreach ($config->keys[$keyGroup] as $item) {
                $key       = $item['secret'] ?? $item['public'];
                $algorithm = $item['alg'];

                $keys[$item['kid']] = new Key($key, $algorithm);
            }
        }

        try {
            return JWT::decode($encodedToken, $keys);
        } catch (BeforeValidException|ExpiredException $e) {
            throw new RuntimeException('Expired JWT: ' . $e->getMessage(), 0, $e);
        } catch (
            InvalidArgumentException|DomainException|UnexpectedValueException
            |SignatureInvalidException $e
        ) {
            throw new RuntimeException('Invalid JWT: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function encode(array $payload, $key, ?array $headers = null): string
    {
        $keyGroup = $key;

        $config = config(AuthJWT::class);

        if (isset($config->keys[$keyGroup][0]['secret'])) {
            $key = $config->keys[$keyGroup][0]['secret'];
        } else {
            $passphrase = $config->keys[$keyGroup][0]['passphrase'] ?? '';

            if ($passphrase !== '') {
                $key = openssl_pkey_get_private(
                    $config->keys[$keyGroup][0]['private'],
                    $passphrase
                );
            } else {
                $key = $config->keys[$keyGroup][0]['private'];
            }
        }

        $algorithm = $config->keys[$keyGroup][0]['alg'];

        $keyId = $config->keys[$keyGroup][0]['kid'] ?? null;
        if ($keyId === '') {
            $keyId = null;
        }

        return JWT::encode($payload, $key, $algorithm, $keyId, $headers);
    }
}
