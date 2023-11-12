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
     * This is an array of keys which will facilitate key rotation. Valid
     *  keyTitles must include only [a-zA-z0-9_] and should be kept to a
     *  max of 8 characters.
     *
     * The valid and old/deprecated keys are identified using $hmacKeyIndex
     *  and $hmacDeprecatedKeyIndex.
     *
     * @param array<string, string>|string $hmacEncryptionKey ['keyTitle' => 'keyValue']
     *
     * @see https://codeigniter.com/user_guide/libraries/encryption.html
     */
    public $hmacEncryptionKey = [
        'k1' => '',
    ];

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
     * This is an array of drivers values.  The keys MUST match and correlate
     *  to the $hmacEncryptionKey array keys.
     *
     * @param array<string, string>|string $hmacEncryptionDriver ['keyTitle' => 'driverValue']
     *
     * @see https://codeigniter.com/user_guide/libraries/encryption.html
     */
    public $hmacEncryptionDriver = [
        'k1' => 'OpenSSL',
    ];

    /**
     * --------------------------------------------------------------------
     * HMAC encryption digest
     * --------------------------------------------------------------------
     * Digest to be used when encrypting HMAC Secret Key for storage.
     *
     * e.g. 'SHA512' or 'SHA256'. Default value is 'SHA512'.
     *
     * This is an array of digest values.  The keys MUST match and correlate
     *  to the $hmacEncryptionKey array keys.
     *
     * @param array<string, string>|string $hmacEncryptionDigest ['keyTitle' => 'digestValue']
     *
     * @see https://codeigniter.com/user_guide/libraries/encryption.html
     */
    public $hmacEncryptionDigest = [
        'k1' => 'SHA512',
    ];

    /**
     * --------------------------------------------------------------------
     * HMAC encryption key selector
     * --------------------------------------------------------------------
     * This identifies which encryption key {$hmacEncryptionKey} is active
     *  and valid.
     */
    public string $hmacKeyIndex = 'k1';

    /**
     * --------------------------------------------------------------------
     * HMAC encryption key deprecated selector
     * --------------------------------------------------------------------
     * This identifies which encryption key {$hmacEncryptionKey} is
     *  recently deprecated.  This is required and used when rotating keys.
     *  Effectively, this is the index selector for the old key.
     */
    public string $hmacDeprecatedKeyIndex = '';

    public function __construct()
    {
        parent::__construct();

        if (is_string($this->hmacEncryptionKey)) {
            $array = json_decode($this->hmacEncryptionKey, true);
            if (is_array($array)) {
                $this->hmacEncryptionKey = $array;
            }
        }
        if (is_string($this->hmacEncryptionDriver)) {
            $array = json_decode($this->hmacEncryptionDriver, true);
            if (is_array($array)) {
                $this->hmacEncryptionDriver = $array;
            }
        }
        if (is_string($this->hmacEncryptionDigest)) {
            $array = json_decode($this->hmacEncryptionDigest, true);
            if (is_array($array)) {
                $this->hmacEncryptionDigest = $array;
            }
        }
    }

    /**
     * Override parent initEnvValue() to allow for direct setting to array properties values from ENV
     *
     * In order to set array properties via ENV vars we need to set the property to a string value first.
     *
     * @param mixed $property
     */
    protected function initEnvValue(&$property, string $name, string $prefix, string $shortPrefix): void
    {
        switch ($name) {
            case 'hmacEncryptionKey':
            case 'hmacEncryptionDriver':
            case 'hmacEncryptionDigest':
                // if attempting to set property from ENV, first set to empty string
                if ((bool) $this->getEnvValue($name, $prefix, $shortPrefix)) {
                    $property = '';
                }
        }

        parent::initEnvValue($property, $name, $prefix, $shortPrefix);
    }
}
