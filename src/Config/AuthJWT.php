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
     * The default payload items.
     *
     * @var array<string, string>
     */
    public array $defaultClaims = [
        'iss' => '<Issuer of the JWT>',
    ];

    /**
     * The secret key. Needs more than 256 bits random string.
     * E.g., $ php -r 'echo base64_encode(random_bytes(32));'
     */

    /**
     * The Keys
     */
    public array $keys = [
        'default' => [
            // Symmetric Key
            [
                'kid' => '',      // (Optional) Key ID.
                'alg' => 'HS256', // algorithm.
                // Set secret random string. Needs more than 256 bits.
                // E.g., $ php -r 'echo base64_encode(random_bytes(32));'
                'secret' => '<Set secret random string>',
            ],
            // (Not implemented) Asymmetric Key
            // [
            //     'kid'     => '',      // (Optional) Key ID.
            //     'alg'     => 'RS256', // algorithm.
            //     'public'  => '',      // Public Key
            //     'private' => '',      // Private Key
            // ],
        ],
    ];

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