<?php

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\Actions\CreateIdentityTrait;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\DatabaseException;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class CreateIdentityTraitTest extends TestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    protected $refresh = true;

    public function testCreateIdentityThrowsExceptionIfUniqueCodeIsNotGot()
    {
        $this->expectException(DatabaseException::class);

        /** @var User $user */
        $user = fake(UserModel::class, ['id' => '1']);
        fake(UserIdentityModel::class, ['user_id' => 2, 'type' => 'email_2fa', 'secret' => '666666']);

        $trait = new class () {
            use CreateIdentityTrait;

            private string $type = 'email_2fa';

            public function afterAttempt(User $user): void
            {
                $this->createCodeIdentity($user, 'login', lang('Auth.need2FA'));
            }

            private function generateSecretCode(): string
            {
                // always returns the same code.
                return '666666';
            }
        };

        $trait->afterAttempt($user);
    }
}
