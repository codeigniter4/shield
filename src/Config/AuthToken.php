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
 * Configuration for Token Auth and HMAC Auth
 */
class AuthToken extends BaseConfig
{
    /**
     * --------------------------------------------------------------------
     * Record Login Attempts for Token Auth and HMAC Auth
     * --------------------------------------------------------------------
     * Specify which login attempts are recorded in the database.
     *
     * Valid values are:
     * - Auth::RECORD_LOGIN_ATTEMPT_NONE
     * - Auth::RECORD_LOGIN_ATTEMPT_FAILURE
     * - Auth::RECORD_LOGIN_ATTEMPT_ALL
     */
    public int $recordLoginAttempt = Auth::RECORD_LOGIN_ATTEMPT_FAILURE;

    /**
     * --------------------------------------------------------------------
     * Name of Authenticator Header
     * --------------------------------------------------------------------
     * The name of Header that the Authorization token should be found.
     * According to the specs, this should be `Authorization`, but rare
     * circumstances might need a different header.
     */
    public array $authenticatorHeader = [
        'tokens' => 'Authorization',
        'hmac'   => 'Authorization',
    ];

    /**
     * --------------------------------------------------------------------
     * Unused Token Lifetime
     * --------------------------------------------------------------------
     * Determines the amount of time, in seconds, that an unused token can
     * be used.
     */
    public int $unusedTokenLifetime = YEAR;

    /**
     * --------------------------------------------------------------------
     * Secret2 storage character limit
     * --------------------------------------------------------------------
     * Database size limit for the identities 'secret2' field.
     */
    public int $secret2StorageLimit = 255;

    /**
     * --------------------------------------------------------------------
     * HMAC secret key byte size
     * --------------------------------------------------------------------
     * Specify in integer the desired byte size of the
     * HMAC SHA256 byte size
     */
    public int $hmacSecretKeyByteSize = 32;

    /**
     * --------------------------------------------------------------------
     * HMAC encryption key
     * --------------------------------------------------------------------
     * Key to be used when encrypting HMAC Secret Key for storage.
     *
     * @see https://codeigniter.com/user_guide/libraries/encryption.html
     */
    public string $hmacEncryptionKey = '';

    /**
     * --------------------------------------------------------------------
     * HMAC encryption driver
     * --------------------------------------------------------------------
     * Driver to be used when encrypting HMAC Secret Key for storage.
     *
     * Available drivers:
     * OpenSSL
     * Sodium
     *
     * @see https://codeigniter.com/user_guide/libraries/encryption.html
     */
    public string $hmacEncryptionDriver = 'OpenSSL';

    /**
     * --------------------------------------------------------------------
     * HMAC encryption digest
     * --------------------------------------------------------------------
     * Digest to be used when encrypting HMAC Secret Key for storage.
     *
     * e.g. 'SHA512' or 'SHA256'. Default value is 'SHA512'.
     *
     * @see https://codeigniter.com/user_guide/libraries/encryption.html
     */
    public string $hmacEncryptionDigest = 'SHA512';
}
