<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\DatabaseException;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class UserIdentityModelTest extends TestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    protected $refresh = true;

    private function createUserIdentityModel(): UserIdentityModel
    {
        return new UserIdentityModel();
    }

    public function testCreateDuplicateRecordThrowsException(): void
    {
        $this->expectException(DatabaseException::class);

        $model = $this->createUserIdentityModel();

        // "type and secret" are unique.
        $model->create([
            'user_id' => 1,
            'type'    => Session::ID_TYPE_EMAIL_ACTIVATE,
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
        $model->create([
            'user_id' => 1,
            'type'    => Session::ID_TYPE_EMAIL_ACTIVATE,
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
    }

    public function testCreateCodeIdentityThrowsExceptionIfUniqueCodeIsNotGot(): void
    {
        $this->expectException(DatabaseException::class);

        /** @var User $user */
        $user = fake(UserModel::class, ['id' => '1']);
        fake(UserIdentityModel::class, ['user_id' => 2, 'type' => Session::ID_TYPE_EMAIL_ACTIVATE, 'secret' => '666666']);

        $model = $this->createUserIdentityModel();

        $generator = static fn (): string => '666666';

        $model->createCodeIdentity(
            $user,
            [
                'type'  => Session::ID_TYPE_EMAIL_ACTIVATE,
                'name'  => 'register',
                'extra' => lang('Auth.needVerification'),
            ],
            $generator
        );
    }
}
