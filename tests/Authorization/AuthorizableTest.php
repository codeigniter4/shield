<?php

namespace Test\Authorization;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\DatabaseTestTrait;
use Sparks\Shield\Authorization\AuthorizationException;
use Sparks\Shield\Models\UserModel;
use Tests\Support\TestCase;

class AuthorizableTest extends TestCase
{
	use DatabaseTestTrait;

	protected $refresh   = true;
	protected $namespace = 'Sparks\Shield';

	/**
	 * @var UserModel
	 */
	protected $model;

	public function setUp(): void
	{
		parent::setUp();

		$this->model = new UserModel();

		// Refresh should take care of this....
		db_connect()->table('auth_groups_users')->truncate();
		db_connect()->table('auth_permissions_users')->truncate();
	}

	public function testAddGroupWithNoExistingGroups()
	{
		$user = fake(UserModel::class);

		$user->addGroup('admin', 'beta');
		// Make sure it doesn't record duplicates
		$user->addGroup('admin', 'beta');

		$this->seeInDatabase('auth_groups_users', [
			'user_id' => $user->id,
			'groups'  => serialize(['admin', 'beta']),
		]);

		$this->assertTrue($user->inGroup('admin'));
		$this->assertTrue($user->inGroup('beta'));
		$this->assertFalse($user->inGroup('user'));
	}

	public function testAddGroupWithExistingGroups()
	{
		$user = fake(UserModel::class);

		$this->hasInDatabase('auth_groups_users', [
			'user_id'    => $user->id,
			'groups'     => serialize(['admin', 'superadmin']),
			'created_at' => Time::now()->toDateTimeString(),
		]);

		$user->addGroup('admin', 'beta');
		// Make sure it doesn't record duplicates
		$user->addGroup('admin', 'beta');

		$this->seeInDatabase('auth_groups_users', [
			'user_id' => $user->id,
			'groups'  => serialize(['admin', 'superadmin', 'beta']),
		]);

		$this->assertTrue($user->inGroup('admin'));
		$this->assertTrue($user->inGroup('beta'));
		$this->assertFalse($user->inGroup('user'));
	}

	public function testAddGroupWithUnknownGroup()
	{
		$this->expectException(AuthorizationException::class);

		$user = fake(UserModel::class);

		$user->addGroup('admin', 'foo');
	}

	public function testRemoveGroupNoGroups()
	{
		$user = fake(UserModel::class);

		$this->assertEquals($user, $user->removeGroup('admin'));
	}

	public function testRemoveGroupExistingGroup()
	{
		$user = fake(UserModel::class);

		$this->hasInDatabase('auth_groups_users', [
			'user_id'    => $user->id,
			'groups'     => serialize(['admin']),
			'created_at' => Time::now()->toDateTimeString(),
		]);

		$user->removeGroup('admin');
		$this->assertEmpty($user->getGroups());
		$this->dontSeeInDatabase('auth_groups_users', [
			'user_id' => $user->id,
			'groups'  => serialize(['admin']),
		]);
	}

	public function testAddPermissionWithNoExistingPermissions()
	{
		$user = fake(UserModel::class);

		$user->addPermission('admin.access', 'beta.access');
		// Make sure it doesn't record duplicates
		$user->addPermission('admin.access', 'beta.access');

		$this->seeInDatabase('auth_permissions_users', [
			'user_id'     => $user->id,
			'permissions' => serialize(['admin.access', 'beta.access']),
		]);

		$this->assertTrue($user->can('admin.access'));
		$this->assertTrue($user->can('beta.access'));
		$this->assertFalse($user->can('user.manage'));
	}

	public function testAddPermissionWithExistingPermissions()
	{
		$user = fake(UserModel::class);

		$this->hasInDatabase('auth_permissions_users', [
			'user_id'     => $user->id,
			'permissions' => serialize(['admin.access', 'users.manage']),
			'created_at'  => Time::now()->toDateTimeString(),
		]);

		$user->addPermission('admin.access', 'beta.access');
		// Make sure it doesn't record duplicates
		$user->addPermission('admin.access', 'beta.access');

		$this->seeInDatabase('auth_permissions_users', [
			'user_id'     => $user->id,
			'permissions' => serialize(['admin.access', 'users.manage', 'beta.access']),
		]);

		$this->assertTrue($user->can('admin.access'));
		$this->assertTrue($user->can('beta.access'));
		$this->assertTrue($user->can('users.manage'));
	}

	public function testAddPermissionsWithUnknownPermission()
	{
		$this->expectException(AuthorizationException::class);

		$user = fake(UserModel::class);

		$user->addPermission('admin.access', 'foo');
	}

	public function testRemovePermissionNoPermissions()
	{
		$user = fake(UserModel::class);

		$this->assertEquals($user, $user->removePermission('Admin.access'));
	}

	public function testRemovePermissionExistingPermissions()
	{
		$user = fake(UserModel::class);

		$this->hasInDatabase('auth_permissions_users', [
			'user_id'     => $user->id,
			'permissions' => serialize(['admin.access']),
			'created_at'  => Time::now()->toDateTimeString(),
		]);

		$user->removePermission('admin.access');
		$this->assertEmpty($user->getPermissions());
		$this->dontSeeInDatabase('auth_permissions_users', [
			'user_id'     => $user->id,
			'permissions' => serialize(['admin.access']),
		]);
	}

	public function testCanCascadesToGroupsSimple()
	{
		$user = fake(UserModel::class);
		$user->addGroup('admin');

		$this->assertTrue($user->can('admin.access'));
	}

	public function testCanCascadesToGroupsWithWildcards()
	{
		$user = fake(UserModel::class);
		$user->addGroup('superadmin');

		$this->assertTrue($user->can('admin.access'));
	}
}
