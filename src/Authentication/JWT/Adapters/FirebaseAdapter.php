<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\JWT\Adapters;

use CodeIgniter\Shield\Authentication\JWT\JWSAdapterInterface;
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

class FirebaseAdapter implements JWSAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public static function decode(string $encodedToken, $keyset): stdClass
    {
        $config = config(AuthJWT::class);

        $configKeys = $config->keys[$keyset];

        if (count($configKeys) === 1) {
            $key       = $configKeys[0]['secret'] ?? $configKeys[0]['public'];
            $algorithm = $configKeys[0]['alg'];

            $keys = new Key($key, $algorithm);
        } else {
            $keys = [];

            foreach ($config->keys[$keyset] as $item) {
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
    public static function encode(array $payload, $keyset, ?array $headers = null): string
    {
        $config = config(AuthJWT::class);

        if (isset($config->keys[$keyset][0]['secret'])) {
            $key = $config->keys[$keyset][0]['secret'];
        } else {
            $passphrase = $config->keys[$keyset][0]['passphrase'] ?? '';

            if ($passphrase !== '') {
                $key = openssl_pkey_get_private(
                    $config->keys[$keyset][0]['private'],
                    $passphrase
                );
            } else {
                $key = $config->keys[$keyset][0]['private'];
            }
        }

        $algorithm = $config->keys[$keyset][0]['alg'];

        $keyId = $config->keys[$keyset][0]['kid'] ?? null;
        if ($keyId === '') {
            $keyId = null;
        }

        return JWT::encode($payload, $key, $algorithm, $keyId, $headers);
    }
}
