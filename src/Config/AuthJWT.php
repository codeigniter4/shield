<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
     * --------------------------------------------------------------------
     * The Default Payload Items
     * --------------------------------------------------------------------
     * All JWTs will have these claims in the payload.
     *
     * @var array<string, string>
     */
    public array $defaultClaims = [
        'iss' => '<Issuer of the JWT>',
    ];

    /**
     * --------------------------------------------------------------------
     * The Keys
     * --------------------------------------------------------------------
     * The key of the array is the key group name.
     * The first key of the group is used for signing.
     *
     * @var array<string, array<int, array<string, string>>>
     * @phpstan-var array<string, list<array<string, string>>>
     */
    public array $keys = [
        'default' => [
            // Symmetric Key
            [
                'kid' => '',      // Key ID. Optional if you have only one key.
                'alg' => 'HS256', // algorithm.
                // Set secret random string. Needs at least 256 bits for HS256 algorithm.
                // E.g., $ php -r 'echo base64_encode(random_bytes(32));'
                'secret' => '<Set secret random string>',
            ],
            // Asymmetric Key
            // [
            //     'kid'        => '',      // Key ID. Optional if you have only one key.
            //     'alg'        => 'RS256', // algorithm.
            //     'public'     => '',      // Public Key
            //     'private'    => '',      // Private Key
            //     'passphrase' => ''       // Passphrase
            // ],
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Time To Live (in seconds)
     * --------------------------------------------------------------------
     * Specifies the amount of time, in seconds, that a token is valid.
     */
    public int $timeToLive = HOUR;

    /**
     * --------------------------------------------------------------------
     * Record Login Attempts
     * --------------------------------------------------------------------
     * Whether login attempts are recorded in the database.
     *
     * Valid values are:
     * - Auth::RECORD_LOGIN_ATTEMPT_NONE
     * - Auth::RECORD_LOGIN_ATTEMPT_FAILURE
     * - Auth::RECORD_LOGIN_ATTEMPT_ALL
     */
    public int $recordLoginAttempt = Auth::RECORD_LOGIN_ATTEMPT_FAILURE;
}
