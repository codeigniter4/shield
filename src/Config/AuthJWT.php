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
     * The payload items that all JWT have.
     *
     * @var string[]
     * @phpstan-var array<string, string>
     */
    public array $claims = [
        'iss' => '<Issuer of the JWT>',
        'aud' => '<Audience of the JWT>',
    ];

    /**
     * The secret key. Needs more than 256 bits random string.
     * E.g., $ php -r 'echo base64_encode(random_bytes(32));'
     */
    public string $secretKey = '<Set secret random string like MQ4GfWut1OYZxPY9fXAIq2YP6KzTSKOGNS7dJNcRrR8=>';

    /**
     * JWT Signing Algorithms.
     */
    public string $algorithm = 'HS256';

    /**
     * Specifies the amount of time, in seconds, that a token is valid.
     */
    public int $timeToLive = HOUR;
}
