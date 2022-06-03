<?php

namespace CodeIgniter\Shield\Authentication\Authenticators\JWT;

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
     * Decode JWT
     *
     * @param string $key
     *
     * @return stdClass Payload
     */
    public static function decode(string $encodedToken, $key, string $algorithm): stdClass
    {
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
     * Issues JWT
     *
     * @param string $key
     */
    public static function generate(array $payload, $key, string $algorithm): string
    {
        return JWT::encode($payload, $key, $algorithm);
    }
}
