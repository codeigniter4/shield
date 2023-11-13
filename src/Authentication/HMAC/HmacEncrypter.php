<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\HMAC;

use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Config\AuthToken;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use Config\Encryption;
use Config\Services;
use Exception;

/**
 * HMAC Encrypter class
 *
 * This class handles the setup and configuration of the HMAC Encryption
 */
class HmacEncrypter
{
    /**
     * Codeigniter Encrypter
     */
    private EncrypterInterface $encrypter;

    /**
     * Auth Token config
     */
    private AuthToken $authConfig;

    /**
     * Selected Key Index
     */
    private string $keyIndex;

    /**
     * Constructor
     * Setup encryption configuration
     *
     * @param bool $deprecatedKey Use the deprecated key (useful when re-encrypting on key rotation) [default: false]
     */
    public function __construct(bool $deprecatedKey = false)
    {
        /** @var AuthToken $authConfig */
        $authConfig = config('AuthToken');
        $config     = new Encryption();

        //        // identify which encryption key should be used
        //        $this->keyIndex = $deprecatedKey ? $authConfig->hmacDeprecatedKeyIndex : $authConfig->hmacKeyIndex;
        //
        //        $config->key    = $authConfig->hmacEncryptionKey[$this->keyIndex];
        //        $config->driver = $authConfig->hmacEncryptionDriver[$this->keyIndex];
        //        $config->digest = $authConfig->hmacEncryptionDigest[$this->keyIndex];

        // identify which encryption key should be used
        $this->keyIndex = $deprecatedKey ? $authConfig->hmacEncryption['deprecatedKey'] : $authConfig->hmacEncryption['currentKey'];

        $config->key    = $authConfig->hmacEncryption['key'][$this->keyIndex];
        $config->driver = $authConfig->hmacEncryption['driver'][$this->keyIndex];
        $config->digest = $authConfig->hmacEncryption['digest'][$this->keyIndex];

        $this->authConfig = $authConfig;
        // decrypt secret key so signature can be validated
        $this->encrypter = Services::encrypter($config);
    }

    /**
     * Decrypt
     *
     * @param string $encString Encrypted string
     *
     * @return string Raw decrypted string
     *
     * @throws EncryptionException
     */
    public function decrypt(string $encString): string
    {
        // Removes `$b6$keyIndex$` for base64 format.
        $encString = substr($encString, strlen($this->keyIndex) + 5);

        return $this->encrypter->decrypt(base64_decode($encString, true));
    }

    /**
     * Encrypt
     *
     * @param string $rawString Raw string to encrypt
     *
     * @return string Encrypted string
     *
     * @throws EncryptionException
     * @throws RuntimeException
     */
    public function encrypt(string $rawString): string
    {
        $encryptedString = '$b6$' . $this->keyIndex . '$' . base64_encode($this->encrypter->encrypt($rawString));

        if (strlen($encryptedString) > $this->authConfig->secret2StorageLimit) {
            throw new RuntimeException('Encrypted key too long. Unable to store value.');
        }

        return $encryptedString;
    }

    /**
     * Check if the string already encrypted
     */
    public function isEncrypted(string $string): bool
    {
        return (bool) preg_match('/^\$b6\$/', $string);
    }

    /**
     * Check if the string already encrypted
     */
    public function isEncryptedWithSetKey(string $string): bool
    {
        return (bool) preg_match('/^\$b6\$' . $this->keyIndex . '\$/', $string);
        //        return str_starts_with($string, '$b6$' . $this->keyIndex . '$');
    }

    /**
     * Generate Key
     *
     * @return string Secret Key in base64 format
     *
     * @throws Exception
     */
    public function generateSecretKey(): string
    {
        return base64_encode(random_bytes($this->authConfig->hmacSecretKeyByteSize));
    }
}
