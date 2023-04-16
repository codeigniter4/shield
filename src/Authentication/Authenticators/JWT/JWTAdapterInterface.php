<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\Authenticators\JWT;

use stdClass;

interface JWTAdapterInterface
{
    /**
     * Issues JWT (JWS)
     *
     * @param array<mixed>               $payload   The payload.
     * @param string                     $key       The secret key.
     * @param string                     $algorithm Supported algorithms:
     *                                              'ES384','ES256', 'ES256K',
     *                                              'HS256', 'HS384', 'HS512',
     *                                              'RS256', 'RS384', 'RS512'
     * @param string|null                $keyId     The key ID.
     * @param array<string, string>|null $headers   An array with header elements to attach.
     */
    public static function generate(
        array $payload,
        $key,
        string $algorithm,
        ?string $keyId = null,
        ?array $headers = null
    ): string;

    /**
     * Decode JWT (JWS)
     *
     * @param string $key The secret key.
     *
     * @return stdClass Payload
     */
    public static function decode(string $encodedToken, $key, string $algorithm): stdClass;
}
