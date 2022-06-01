<?php

namespace CodeIgniter\Shield\Authentication\Authenticators\JWT;

use stdClass;

interface JWTDecoderInterface
{
    /**
     * Decode JWT
     *
     * @return stdClass Payload
     */
    public static function decode(string $encodedToken): stdClass;
}
