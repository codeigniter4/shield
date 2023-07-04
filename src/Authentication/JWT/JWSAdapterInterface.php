<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\JWT;

use stdClass;

interface JWSAdapterInterface
{
    /**
     * Issues Signed JWT (JWS)
     *
     * @param array<mixed>               $payload The payload.
     * @param string                     $keyset  The key group.
     *                                            The array key of Config\AuthJWT::$keys.
     * @param array<string, string>|null $headers An array with header elements to attach.
     *
     * @return string JWT (JWS)
     */
    public function encode(array $payload, $keyset, ?array $headers = null): string;

    /**
     * Decode Signed JWT (JWS)
     *
     * @param string $keyset The key group. The array key of Config\AuthJWT::$keys.
     *
     * @return stdClass Payload
     */
    public function decode(string $encodedToken, $keyset): stdClass;
}
