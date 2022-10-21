<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;

/**
 * JWT Authenticator Configuration
 */
class AuthJWT extends BaseConfig
{
    /**
     * These are the default values when you generate and validate JWT
     *
     * - claims      The payload items that all JWT have.
     * - secretKey   The secret key. Needs more than 256 bits random string.
     *               E.g., $ php -r 'echo base64_encode(random_bytes(32));'
     * - algorithm   JWT Signing Algorithms.
     * - timeToLive  Specifies the amount of time, in seconds, that a token is valid.
     *
     * @var array<string, array<string, array|bool|int|string>|bool|int|string>
     */
    public array $config = [
        'claims' => [
            'iss' => '<Issuer of the JWT>',
            'aud' => '<Audience of the JWT>',
        ],
        'secretKey'  => '<Set secret random string like MQ4GfWut1OYZxPY9fXAIq2YP6KzTSKOGNS7dJNcRrR8=>',
        'algorithm'  => 'HS256',
        'timeToLive' => HOUR,
    ];
}
