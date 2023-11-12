<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\Shield\Authentication\HMAC\HmacEncrypter;
use CodeIgniter\Shield\Commands\Hmac;
use CodeIgniter\Shield\Config\AuthToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Test\MockInputOutput;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class HmacTest extends DatabaseTestCase
{
    private ?MockInputOutput $io = null;

    public function testEncrypt(): void
    {
        $idModel = new UserIdentityModel();

        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateHmacToken('foo');

        $rawSecretKey   = $token->rawSecretKey;
        $token->secret2 = $rawSecretKey;

        $idModel->save($token);
        $tokenCheck = $idModel->find($token->id);

        $this->assertSame($rawSecretKey, $tokenCheck->secret2);

        $this->assertNotFalse(command('shield:hmac encrypt'));

        $tokenCheck = $idModel->find($token->id);

        $encrypter    = new HmacEncrypter();
        $decryptedKey = $encrypter->decrypt($tokenCheck->secret2);

        $this->assertSame($rawSecretKey, $decryptedKey);
    }

    public function testDecrypt(): void
    {
        $idModel = new UserIdentityModel();

        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateHmacToken('foo');

        $rawSecretKey = $token->rawSecretKey;

        $this->assertNotFalse(command('shield:hmac decrypt'));

        $token->secret2 = $rawSecretKey;

        $idModel->save($token);
        $tokenCheck = $idModel->find($token->id);

        $this->assertSame($rawSecretKey, $tokenCheck->secret2);
    }

    public function testReEncrypt(): void
    {
        $idModel = new UserIdentityModel();

        // generate first token
        /** @var User $user */
        $user   = fake(UserModel::class);
        $token1 = $user->generateHmacToken('foo');

        // update config, rotate keys
        /** @var AuthToken $config */
        $config = config('AuthToken');

        $config->hmacKeyIndex           = 'k2';
        $config->hmacDeprecatedKeyIndex = 'k1';

        // new key generated with updated encryption
        $token2 = $user->generateHmacToken('bar');

        $this->setMockIo([]);
        $this->assertNotFalse(command('shield:hmac reencrypt'));

        $resultsString = $this->io->getOutputs();
        $results       = explode("\n", trim($resultsString));

        // verify that only 1 key needed to be re-encrypted
        $this->assertCount(2, $results);
        $this->assertSame('id: 1, Re-encrypted.', $results[0]);
        $this->assertSame('id: 2, not re-encrypted, skipped.', $results[1]);

        $encrypter = new HmacEncrypter();

        $tokenCheck1        = $idModel->find($token1->id);
        $descryptSecretKey1 = $encrypter->decrypt($tokenCheck1->secret2);
        $this->assertSame($token1->rawSecretKey, $descryptSecretKey1);

        $tokenCheck2        = $idModel->find($token2->id);
        $descryptSecretKey2 = $encrypter->decrypt($tokenCheck2->secret2);
        $this->assertSame($token2->rawSecretKey, $descryptSecretKey2);
    }

    /**
     * Set MockInputOutput and user inputs.
     *
     * @param array<int, string> $inputs User inputs
     * @phpstan-param list<string> $inputs
     */
    private function setMockIo(array $inputs): void
    {
        $this->io = new MockInputOutput();
        $this->io->setInputs($inputs);
        Hmac::setInputOutput($this->io);
    }
}
