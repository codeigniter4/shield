<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * JWT Authenticator Configuration
 */
class AuthJWT extends BaseConfig
{
    /**
     * --------------------------------------------------------------------
     * Name of Authenticator Header
     * --------------------------------------------------------------------
     * The name of Header that the Authorization token should be found.
     * According to the specs, this should be `Authorization`, but rare
     * circumstances might need a different header.
     */
    public string $authenticatorHeader = 'Authorization';

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

    /**
     * Whether login attempts are recorded in the database.
     *
     * Valid values are:
     * - Auth::RECORD_LOGIN_ATTEMPT_NONE
     * - Auth::RECORD_LOGIN_ATTEMPT_FAILURE
     * - Auth::RECORD_LOGIN_ATTEMPT_ALL
     */
    public int $recordLoginAttempt = Auth::RECORD_LOGIN_ATTEMPT_FAILURE;
}
