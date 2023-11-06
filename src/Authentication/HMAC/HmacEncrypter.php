<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\HMAC;

use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Encryption\Exceptions\EncryptionException;
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
     * Constructor
     * Setup encryption configuration
     */
    public function __construct()
    {
        $this->authConfig = config('AuthToken');
        $config           = new Encryption();

        $config->key    = $this->authConfig->hmacEncryptionKey;
        $config->driver = $this->authConfig->hmacEncryptionDriver;
        $config->digest = $this->authConfig->hmacEncryptionDigest;

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
        // Removes `$b6$` for base64 format.
        $encString = substr($encString, 4);

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
        $encryptedString = '$b6$' . base64_encode($this->encrypter->encrypt($rawString));

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
        return str_starts_with($string, '$b6$');
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
