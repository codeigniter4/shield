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
     * HMAC encryption Keys
     * --------------------------------------------------------------------
     * This sets the key to be used when encrypting a user's HMAC Secret Key.
     *
     * 'key' is an array of keys which will facilitate key rotation. Valid
     *  keyTitles must include only [a-zA-z0-9_] and should be kept to a
     *  max of 8 characters.
     *
     * 'driver' is used when encrypting HMAC Secret Key for storage.
     *    Available drivers:
     *      - OpenSSL
     *      - Sodium
     *
     * This is an array of drivers values.  The keys MUST match and correlate
     *  to the 'key' array keys.
     *
     * 'digest' is used when encrypting HMAC Secret Key for storage.
     *    e.g. 'SHA512' or 'SHA256'. Default value is 'SHA512'.
     *
     * This is an array of digest values.  The keys MUST match and correlate
     *  to the 'key' array keys.
     *
     * The valid and old/deprecated keys are identified using 'currentKey'
     *  and 'deprecatedKey'.
     *
     * 'deprecatedKey' reflects which 'key' is recently deprecated.  This
     *  is required and used when rotating keys. Effectively, this is the
     *  index selector for the old key.
     *
     * @see https://codeigniter.com/user_guide/libraries/encryption.html
     */
    public array $hmacEncryption = [
        'key'           => ['k1' => ''],
        'driver'        => ['k1' => 'OpenSSL'],
        'digest'        => ['k1' => 'SHA512'],
        'currentKey'    => 'k1',
        'deprecatedKey' => '',
    ];

    /**
     * AuthToken Config Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $overwriteHmacEncryptionFields = [
            'key',
            'driver',
            'digest',
        ];

        foreach ($overwriteHmacEncryptionFields as $fieldName) {
            if (is_string($this->hmacEncryption[$fieldName])) {
                $array = json_decode($this->hmacEncryption[$fieldName], true);
                if (is_array($array)) {
                    $this->hmacEncryption[$fieldName] = $array;
                }
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
            case 'hmacEncryption.key':
            case 'hmacEncryption.driver':
            case 'hmacEncryption.digest':
                // if attempting to set property from ENV, first set to empty string
                if ($this->getEnvValue($name, $prefix, $shortPrefix) !== null) {
                    $property = '';
                }
        }

        parent::initEnvValue($property, $name, $prefix, $shortPrefix);
    }
}
