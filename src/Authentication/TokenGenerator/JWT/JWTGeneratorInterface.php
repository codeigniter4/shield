<?php

namespace CodeIgniter\Shield\Authentication\TokenGenerator\JWT;

interface JWTGeneratorInterface
{
    /**
     * Issues JWT
     *
     * @param string $key The secret key.
     */
    public static function generate(array $payload, $key, string $algorithm): string;
}
