<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\HMAC;

use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Shield\Config\AuthToken;
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
     * @param string $hexString Encrypted string in Hex format
     *
     * @return string Raw decrypted string
     */
    public function decrypt(string $hexString): string
    {
        return $this->encrypter->decrypt(hex2bin($hexString));
    }

    /**
     * Encrypt
     *
     * @param string $rawString Raw string to encrypt
     *
     * @return string Encrypted string in hex format
     */
    public function encrypt(string $rawString): string
    {
        return bin2hex($this->encrypter->encrypt($rawString));
    }

    /**
     * Generate Key
     *
     * @return string Secret Key in hexed format
     *
     * @throws Exception
     */
    public function generateSecretKey(): string
    {
        return bin2hex(random_bytes($this->authConfig->hmacSecretKeyByteSize));
    }
}
