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

class Firebase implements JWTDecoderInterface
{
    /**
     * Decode JWT
     *
     * @return stdClass Payload
     */
    public static function decode(string $encodedToken): stdClass
    {
        $config = setting('Auth.jwtConfig');

        $key       = $config['secretKey'];
        $algorithm = $config['algorithm'];

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
}
