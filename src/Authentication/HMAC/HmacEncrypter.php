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
     *
     * @var array<string, EncrypterInterface>
     */
    private array $encrypter;

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

        $this->getEncrypter($this->authConfig->hmacEncryptionCurrentKey);
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
        $matches = [];
        // check for a match
        if (preg_match('/^\$b6\$(\w+?)\$(.+)\z/', $encString, $matches) !== 1) {
            throw new EncryptionException('Unable to decrypt string');
        }

        $encrypter = $this->getEncrypter($matches[1]);

        return $encrypter->decrypt(base64_decode($matches[2], true));
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
        $currentKey = $this->authConfig->hmacEncryptionCurrentKey;

        $encryptedString = '$b6$' . $currentKey . '$' . base64_encode($this->encrypter[$currentKey]->encrypt($rawString));

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
        return preg_match('/^\$b6\$/', $string) === 1;
    }

    /**
     * Check if the string already encrypted with the Current Set Key
     */
    public function isEncryptedWithCurrentKey(string $string): bool
    {
        $currentKey = $this->authConfig->hmacEncryptionCurrentKey;

        return preg_match('/^\$b6\$' . $currentKey . '\$/', $string) === 1;
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

    /**
     * Retrieve encrypter for selected key
     *
     * @param string $encrypterKey Index Key for selected Encrypter
     */
    private function getEncrypter(string $encrypterKey): EncrypterInterface
    {
        if (! isset($this->encrypter[$encrypterKey])) {
            if (! isset($this->authConfig->hmacEncryptionKeys[$encrypterKey]['key'])) {
                throw new RuntimeException('Encryption key does not exist.');
            }

            $config = new Encryption();

            $config->key    = $this->authConfig->hmacEncryptionKeys[$encrypterKey]['key'];
            $config->driver = $this->authConfig->hmacEncryptionKeys[$encrypterKey]['driver'] ?? $this->authConfig->hmacEncryptionDefaultDriver;
            $config->digest = $this->authConfig->hmacEncryptionKeys[$encrypterKey]['digest'] ?? $this->authConfig->hmacEncryptionDefaultDigest;

            $this->encrypter[$encrypterKey] = Services::encrypter($config);
        }

        return $this->encrypter[$encrypterKey];
    }
}
