<?php

declare(strict_types=1);

namespace Tests\Authorization;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authorization\AuthorizationException;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Locale;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthorizableTest extends TestCase
{
    use DatabaseTestTrait;
    use FakeUser;

    protected $refresh = true;
    protected $namespace;
    protected UserModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new UserModel();

        // Refresh should take care of this....
        db_connect()->table('auth_groups_users')->truncate();
        db_connect()->table('auth_permissions_users')->truncate();
    }

    public function testAddGroupWithNoExistingGroups(): void
    {
        $this->user->addGroup('admin', 'beta');
        // Make sure it doesn't record duplicates
        $this->user->addGroup('admin', 'beta');

        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'admin',
        ]);
        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'beta',
        ]);

        $this->assertTrue($this->user->inGroup('admin'));
        $this->assertTrue($this->user->inGroup('beta'));
        $this->assertFalse($this->user->inGroup('user'));
    }

    public function testAddGroupWithExistingGroups(): void
    {
        $this->hasInDatabase('auth_groups_users', [
            'user_id'    => $this->user->id,
            'group'      => 'admin',
            'created_at' => Time::now()->toDateTimeString(),
        ]);
        $this->hasInDatabase('auth_groups_users', [
            'user_id'    => $this->user->id,
            'group'      => 'superadmin',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $this->user->addGroup('admin', 'beta');
        // Make sure it doesn't record duplicates
        $this->user->addGroup('admin', 'beta');

        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'admin',
        ]);
        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'superadmin',
        ]);
        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'beta',
        ]);

        $this->assertTrue($this->user->inGroup('admin'));
        $this->assertTrue($this->user->inGroup('beta'));
        $this->assertFalse($this->user->inGroup('user'));
    }

    public function testAddGroupWithUnknownGroup(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->user->addGroup('admin', 'foo');
    }

    public function testRemoveGroupNoGroups(): void
    {
        $this->assertSame($this->user, $this->user->removeGroup('admin'));
    }

    public function testRemoveGroupExistingGroup(): void
    {
        $this->hasInDatabase('auth_groups_users', [
            'user_id'    => $this->user->id,
            'group'      => 'admin',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $otherUser = fake(UserModel::class);
        $this->hasInDatabase('auth_groups_users', [
            'user_id'    => $otherUser->id,
            'group'      => 'admin',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $this->user->removeGroup('admin');
        $this->assertEmpty($this->user->getGroups());
        $this->dontSeeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'admin',
        ]);

        // Make sure we didn't delete the group from anyone else
        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $otherUser->id,
            'group'   => 'admin',
        ]);
    }

    public function testSyncGroups(): void
    {
        $this->hasInDatabase('auth_groups_users', [
            'user_id'    => $this->user->id,
            'group'      => 'admin',
            'created_at' => Time::now()->toDateTimeString(),
        ]);
        $this->hasInDatabase('auth_groups_users', [
            'user_id'    => $this->user->id,
            'group'      => 'superadmin',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $this->user->syncGroups('admin', 'beta');
        $this->assertSame(['admin', 'beta'], $this->user->getGroups());
        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'admin',
        ]);
        $this->seeInDatabase('auth_groups_users', [
            'user_id' => $this->user->id,
            'group'   => 'beta',
        ]);
    }

    public function testAddPermissionWithNoExistingPermissions(): void
    {
        $this->user->addPermission('admin.access', 'beta.access');
        // Make sure it doesn't record duplicates
        $this->user->addPermission('admin.access', 'beta.access');

        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'admin.access',
        ]);
        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'beta.access',
        ]);

        $this->assertTrue($this->user->can('admin.access'));
        $this->assertTrue($this->user->can('beta.access'));
        $this->assertFalse($this->user->can('user.manage'));
    }

    public function testAddPermissionWithExistingPermissions(): void
    {
        $this->hasInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'admin.access',
            'created_at' => Time::now()->toDateTimeString(),
        ]);
        $this->hasInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'users.manage',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $this->user->addPermission('admin.access', 'beta.access');
        // Make sure it doesn't record duplicates
        $this->user->addPermission('admin.access', 'beta.access');

        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'admin.access',
        ]);
        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'users.manage',
        ]);
        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'beta.access',
        ]);

        $this->assertTrue($this->user->can('admin.access'));
        $this->assertTrue($this->user->can('beta.access'));
        $this->assertTrue($this->user->can('users.manage'));
    }

    public function testAddPermissionsWithUnknownPermission(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->user->addPermission('admin.access', 'foo');
    }

    public function testRemovePermissionNoPermissions(): void
    {
        $this->assertSame($this->user, $this->user->removePermission('Admin.access'));
    }

    public function testRemovePermissionExistingPermissions(): void
    {
        $this->hasInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'admin.access',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $otherUser = fake(UserModel::class);
        $this->hasInDatabase('auth_permissions_users', [
            'user_id'    => $otherUser->id,
            'permission' => 'admin.access',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $this->user->removePermission('admin.access');
        $this->assertEmpty($this->user->getPermissions());
        $this->dontSeeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'admin.access',
        ]);

        // Make sure it didn't delete the other user's permission
        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $otherUser->id,
            'permission' => 'admin.access',
        ]);
    }

    public function testSyncPermissions(): void
    {
        $this->hasInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'admin.access',
            'created_at' => Time::now()->toDateTimeString(),
        ]);
        $this->hasInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'superadmin.access',
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $this->user->syncPermissions('admin.access', 'beta.access');
        $this->assertSame(['admin.access', 'beta.access'], $this->user->getPermissions());
        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'admin.access',
        ]);
        $this->seeInDatabase('auth_permissions_users', [
            'user_id'    => $this->user->id,
            'permission' => 'beta.access',
        ]);
    }

    public function testHasPermission(): void
    {
        $this->user->addPermission('admin.access');

        $this->assertTrue($this->user->hasPermission('admin.access'));
        $this->assertFalse($this->user->hasPermission('beta.access'));
    }

    public function testCanCascadesToGroupsSimple(): void
    {
        $this->user->addGroup('admin');

        $this->assertTrue($this->user->can('admin.access'));
    }

    public function testCanCascadesToGroupsWithWildcards(): void
    {
        $this->user->addGroup('superadmin');

        $this->assertTrue($this->user->can('admin.access'));
    }

    public function testCanGetsInvalidPermission(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid permission: developer');

        $this->user->addGroup('superadmin');

        $this->assertTrue($this->user->can('developer'));
    }

    /**
     * @see https://github.com/codeigniter4/shield/pull/238
     */
    public function testCreatedAtIfDefaultLocaleSetFaWithAddGroup(): void
    {
        $currentLocale = Locale::getDefault();
        Locale::setDefault('fa');

        Time::setTestNow('March 10, 2017', 'America/Chicago');

        $this->user->addGroup('admin');

        $this->seeInDatabase('auth_groups_users', [
            'user_id'    => $this->user->id,
            'group'      => 'admin',
            'created_at' => '2017-03-10 00:00:00',
        ]);

        Locale::setDefault($currentLocale);
        Time::setTestNow();
    }
}
