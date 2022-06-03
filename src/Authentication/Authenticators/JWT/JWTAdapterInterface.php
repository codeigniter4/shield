<?php

namespace CodeIgniter\Shield\Authentication\Authenticators\JWT;

use stdClass;

interface JWTAdapterInterface
{
    /**
     * Issues JWT
     *
     * @param string $key The secret key.
     */
    public static function generate(array $payload, $key, string $algorithm): string;

    /**
     * Decode JWT
     *
     * @param string $key The secret key.
     *
     * @return stdClass Payload
     */
    public static function decode(string $encodedToken, $key, string $algorithm): stdClass;
}
