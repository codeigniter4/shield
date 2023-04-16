<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\Authenticators\JWT;

use stdClass;

interface JWTAdapterInterface
{
    /**
     * Issues JWT (JWS)
     *
     * @param array<mixed>               $payload The payload.
     * @param string                     $key     The key group.
     *                                            The array key of Config\AuthJWT::$keys.
     * @param array<string, string>|null $headers An array with header elements to attach.
     *
     * @return string JWT (JWS)
     */
    public static function encode(
        array $payload,
        $key,
        ?array $headers = null
    ): string;

    /**
     * Decode JWT (JWS)
     *
     * @param string $key The key group. The array key of Config\AuthJWT::$keys.
     *
     * @return stdClass Payload
     */
    public static function decode(string $encodedToken, $key): stdClass;
}
