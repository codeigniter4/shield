<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\Shield\Authentication\HMAC\HmacEncrypter;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class HmacTest extends DatabaseTestCase
{
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
}
