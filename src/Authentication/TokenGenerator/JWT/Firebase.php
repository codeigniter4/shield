<?php

namespace CodeIgniter\Shield\Authentication\TokenGenerator\JWT;

use Firebase\JWT\JWT;

class Firebase implements JWTGeneratorInterface
{
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
