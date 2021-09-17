<?php

namespace Test\Authorization;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\DatabaseTestTrait;
use Sparks\Shield\Authorization\AuthorizationException;
use Sparks\Shield\Models\UserModel;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthorizableTest extends TestCase
{
	use DatabaseTestTrait;
	use FakeUser;

	protected $refresh   = true;
	protected $namespace = null;

	/**
	 * @var UserModel
	 */
	protected $model;

	protected function setUp(): void
	{
		parent::setUp();

		$this->model = new UserModel();

		// Refresh should take care of this....
		db_connect()->table('auth_groups_users')->truncate();
		db_connect()->table('auth_permissions_users')->truncate();
	}

	public function testAddGroupWithNoExistingGroups()
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

	public function testAddGroupWithExistingGroups()
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

	public function testAddGroupWithUnknownGroup()
	{
		$this->expectException(AuthorizationException::class);

		$this->user->addGroup('admin', 'foo');
	}

	public function testRemoveGroupNoGroups()
	{
		$this->assertSame($this->user, $this->user->removeGroup('admin'));
	}

	public function testRemoveGroupExistingGroup()
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

	public function testSyncGroups()
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

		$this->user->syncGroups(['admin', 'beta']);
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

	public function testAddPermissionWithNoExistingPermissions()
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

	public function testAddPermissionWithExistingPermissions()
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

	public function testAddPermissionsWithUnknownPermission()
	{
		$this->expectException(AuthorizationException::class);

		$this->user->addPermission('admin.access', 'foo');
	}

	public function testRemovePermissionNoPermissions()
	{
		$this->assertSame($this->user, $this->user->removePermission('Admin.access'));
	}

	public function testRemovePermissionExistingPermissions()
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

	public function testSyncPermissions()
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

		$this->user->syncPermissions(['admin.access', 'beta.access']);
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

	public function testCanCascadesToGroupsSimple()
	{
		$this->user->addGroup('admin');

		$this->assertTrue($this->user->can('admin.access'));
	}

	public function testCanCascadesToGroupsWithWildcards()
	{
		$this->user->addGroup('superadmin');

		$this->assertTrue($this->user->can('admin.access'));
	}
}
