<?php

namespace Test\Authorization;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\DatabaseTestTrait;
use Sparks\Shield\Authorization\AuthorizationException;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Models\UserModel;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

class AuthorizableTest extends TestCase
{
	use DatabaseTestTrait;
	use FakeUser;

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
		$this->user->addGroup('admin', 'beta');
		// Make sure it doesn't record duplicates
		$this->user->addGroup('admin', 'beta');

		$this->seeInDatabase('auth_groups_users', [
			'user_id' => $this->user->id,
			'groups'  => serialize(['admin', 'beta']),
		]);

		$this->assertTrue($this->user->inGroup('admin'));
		$this->assertTrue($this->user->inGroup('beta'));
		$this->assertFalse($this->user->inGroup('user'));
	}

	public function testAddGroupWithExistingGroups()
	{
		$this->hasInDatabase('auth_groups_users', [
			'user_id'    => $this->user->id,
			'groups'     => serialize(['admin', 'superadmin']),
			'created_at' => Time::now()->toDateTimeString(),
		]);

		$this->user->addGroup('admin', 'beta');
		// Make sure it doesn't record duplicates
		$this->user->addGroup('admin', 'beta');

		$this->seeInDatabase('auth_groups_users', [
			'user_id' => $this->user->id,
			'groups'  => serialize(['admin', 'superadmin', 'beta']),
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
		$this->assertEquals($this->user, $this->user->removeGroup('admin'));
	}

	public function testRemoveGroupExistingGroup()
	{
		$this->hasInDatabase('auth_groups_users', [
			'user_id'    => $this->user->id,
			'groups'     => serialize(['admin']),
			'created_at' => Time::now()->toDateTimeString(),
		]);

		$this->user->removeGroup('admin');
		$this->assertEmpty($this->user->getGroups());
		$this->dontSeeInDatabase('auth_groups_users', [
			'user_id' => $this->user->id,
			'groups'  => serialize(['admin']),
		]);
	}

	public function testAddPermissionWithNoExistingPermissions()
	{
		$this->user->addPermission('admin.access', 'beta.access');
		// Make sure it doesn't record duplicates
		$this->user->addPermission('admin.access', 'beta.access');

		$this->seeInDatabase('auth_permissions_users', [
			'user_id'     => $this->user->id,
			'permissions' => serialize(['admin.access', 'beta.access']),
		]);

		$this->assertTrue($this->user->can('admin.access'));
		$this->assertTrue($this->user->can('beta.access'));
		$this->assertFalse($this->user->can('user.manage'));
	}

	public function testAddPermissionWithExistingPermissions()
	{
		$this->hasInDatabase('auth_permissions_users', [
			'user_id'     => $this->user->id,
			'permissions' => serialize(['admin.access', 'users.manage']),
			'created_at'  => Time::now()->toDateTimeString(),
		]);

		$this->user->addPermission('admin.access', 'beta.access');
		// Make sure it doesn't record duplicates
		$this->user->addPermission('admin.access', 'beta.access');

		$this->seeInDatabase('auth_permissions_users', [
			'user_id'     => $this->user->id,
			'permissions' => serialize(['admin.access', 'users.manage', 'beta.access']),
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
		$this->assertEquals($this->user, $this->user->removePermission('Admin.access'));
	}

	public function testRemovePermissionExistingPermissions()
	{
		$this->hasInDatabase('auth_permissions_users', [
			'user_id'     => $this->user->id,
			'permissions' => serialize(['admin.access']),
			'created_at'  => Time::now()->toDateTimeString(),
		]);

		$this->user->removePermission('admin.access');
		$this->assertEmpty($this->user->getPermissions());
		$this->dontSeeInDatabase('auth_permissions_users', [
			'user_id'     => $this->user->id,
			'permissions' => serialize(['admin.access']),
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
