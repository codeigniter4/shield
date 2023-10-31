<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Config\AuthToken;
use CodeIgniter\Shield\Models\UserIdentityModel;
use Config\Encryption;
use Config\Services;

class EncryptHmacKeys extends Migration
{
    /**
     * Encryptor Interface object
     */
    private EncrypterInterface $encryptor;

    public function __construct(?Forge $forge = null)
    {
        /** @var Auth $authConfig */
        $authConfig = config('Auth');

        if ($authConfig->DBGroup !== null) {
            $this->DBGroup = $authConfig->DBGroup;
        }

        parent::__construct($forge);

        /** @var AuthToken $authTokenConfig */
        $authTokenConfig = config('AuthToken');
        $config          = new Encryption();

        $config->key    = $authTokenConfig->hmacEncryptionKey;
        $config->driver = $authTokenConfig->hmacEncryptionDriver;
        $config->digest = $authTokenConfig->hmacEncryptionDigest;

        // decrypt secret key so signature can be validated
        $this->encryptor = Services::encrypter($config);
    }

    /**
     * {@inheritDoc}
     */
    public function up(): void
    {
        $uIdModel = new UserIdentityModel();

        $updateList     = [];
        $hmacIdentities = $uIdModel->where('type', 'hmac_sha256')->findAll();

        foreach ($hmacIdentities as $identity) {
            $identity->secret2 = bin2hex($this->encryptor->encrypt($identity->secret2));

            $updateList[] = [
                'id'      => $identity->id,
                'secret2' => $identity->secret2,
            ];
        }

        if ($updateList !== []) {
            $uIdModel->updateBatch($updateList, 'id');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down(): void
    {
        $uIdModel = new UserIdentityModel();

        $updateList     = [];
        $hmacIdentities = $uIdModel->where('type', 'hmac_sha256')->findAll();

        foreach ($hmacIdentities as $identity) {
            $identity->secret2 = $this->encryptor->decrypt(hex2bin($identity->secret2));

            $updateList[] = [
                'id'      => $identity->id,
                'secret2' => $identity->secret2,
            ];
        }

        if ($updateList !== []) {
            $uIdModel->updateBatch($updateList, 'id');
        }
    }
}
