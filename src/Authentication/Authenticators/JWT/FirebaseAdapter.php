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

        $config    = config(AuthJWT::class);
        $key       = $config->keys[$keyGroup][0]['secret'];
        $algorithm = $config->keys[$keyGroup][0]['alg'];

        try {
            return JWT::decode($encodedToken, new Key($key, $algorithm));
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
    public static function generate(array $payload, $key, ?array $headers = null): string
    {
        $keyGroup = $key;

        $config    = config(AuthJWT::class);
        $key       = $config->keys[$keyGroup][0]['secret'];
        $algorithm = $config->keys[$keyGroup][0]['alg'];

        $keyId = $config->keys[$keyGroup][0]['kid'] ?? null;
        if ($keyId === '') {
            $keyId = null;
        }

        return JWT::encode($payload, $key, $algorithm, $keyId, $headers);
    }
}
