<?php

namespace Tests\Unit;

use CodeIgniter\Shield\Models\DatabaseException;
use CodeIgniter\Shield\Models\UserIdentityModel;
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

    public function testCreateDuplicateRecordThrowsException()
    {
        $this->expectException(DatabaseException::class);

        $model = new UserIdentityModel();

        // "type and secret" are unique.
        $model->create([
            'user_id' => 1,
            'type'    => 'email_activate',
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
        $model->create([
            'user_id' => 1,
            'type'    => 'email_activate',
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
    }
}
