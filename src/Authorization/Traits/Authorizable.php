<?php

namespace Sparks\Shield\Authorization\Traits;

use CodeIgniter\I18n\Time;
use Sparks\Shield\Authorization\AuthorizationException;

trait Authorizable
{
	/**
	 * @var array|null
	 */
	protected $groupCache;

	/**
	 * @var array|null
	 */
	protected $permissionsCache;

	/**
	 * Adds one or more groups to the current User.
	 *
	 * @param string ...$groups
	 */
	public function addGroup(string ...$groups)
	{
		$this->populateGroups();
		$configGroups = function_exists('setting')
			? array_keys(setting('AuthGroups.groups'))
			: array_keys(config('AuthGroups')->groups);

		$groupCount = count($this->groupCache);

		foreach ($groups as $group)
		{
			$group = strtolower($group);

			// don't allow dupes
			if (in_array($group, $this->groupCache))
			{
				continue;
			}

			// make sure it's a valid group
			if (! in_array($group, $configGroups))
			{
				throw AuthorizationException::forUnknownGroup($group);
			}

			$this->groupCache[] = $group;
		}

		// Only save the results if there's anything new.
		if (count($this->groupCache) > $groupCount)
		{
			$this->saveGroupsOrPermissions('groups');
		}

		return $this;
	}

	/**
	 * Removes one or more groups from the user.
	 *
	 * @param string ...$groups
	 *
	 * @return self
	 */
	public function removeGroup(string ...$groups)
	{
		$this->populateGroups();
		foreach ($groups as &$group)
		{
			$group = strtolower($group);
		}

		// Remove from local cache
		$this->groupCache = array_diff($this->groupCache, $groups);

		// Update the database.
		$this->saveGroupsOrPermissions('groups');

		return $this;
	}

	/**
	 * Returns all groups this user is a part of.
	 *
	 * @return array|null
	 */
	public function getGroups()
	{
		$this->populateGroups();

		return $this->groupCache;
	}

	/**
	 * Returns all permissions this user has
	 * assigned directly to them.
	 *
	 * @return array|null
	 */
	public function getPermissions()
	{
		$this->populatePermissions();

		return $this->permissionsCache;
	}

	/**
	 * Adds one or more permissions to the current user.
	 *
	 * @param string ...$permissions
	 *
	 * @return $this
	 * @throws AuthorizationException
	 */
	public function addPermission(string ...$permissions)
	{
		$this->populatePermissions();
		$configPermissions = function_exists('setting')
			? array_keys(setting('AuthGroups.permissions'))
			: array_keys(config('AuthGroups')->permissions);

		$permissionCount = count($this->permissionsCache);

		foreach ($permissions as $permission)
		{
			$permission = strtolower($permission);

			// don't allow dupes
			if (in_array($permission, $this->permissionsCache))
			{
				continue;
			}

			// make sure it's a valid group
			if (! in_array($permission, $configPermissions))
			{
				throw AuthorizationException::forUnknownPermission($permission);
			}

			$this->permissionsCache[] = $permission;
		}

		// Only save the results if there's anything new.
		if (count($this->permissionsCache) > $permissionCount)
		{
			$this->saveGroupsOrPermissions('permissions');
		}

		return $this;
	}

	/**
	 * Removes one or more permissions from the current user.
	 *
	 * @param string ...$permissions
	 *
	 * @return $this
	 */
	public function removePermission(string ...$permissions)
	{
		$this->populatePermissions();
		foreach ($permissions as &$permission)
		{
			$permission = strtolower($permission);
		}

		// Remove from local cache
		$this->permissionsCache = array_diff($this->permissionsCache, $permissions);

		// Update the database.
		$this->saveGroupsOrPermissions('permissions');

		return $this;
	}

	/**
	 * Checks user permissions and their group permissions
	 * to see if the user has a specific permission.
	 *
	 * @param string $permission
	 *
	 * @return boolean
	 */
	public function can(string $permission): bool
	{
		$this->populatePermissions();
		$permission = strtolower($permission);

		// Check user's permissions
		if (in_array($permission, $this->permissionsCache))
		{
			return true;
		}

		// Check the groups the user belongs to
		$this->populateGroups();

		if (! count($this->groupCache))
		{
			return false;
		}

		$matrix = function_exists('setting')
			? setting('AuthGroups.matrix')
			: config('AuthGroups')->matrix;

		foreach ($this->groupCache as $group)
		{
			// Check exact match
			if (isset($matrix[$group]) && in_array($permission, $matrix[$group]))
			{
				return true;
			}

			// Check wildcard match
			$check = substr($permission, 0, strpos($permission, '.')) . '.*';
			if (isset($matrix[$group]) && in_array($check, $matrix[$group]))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks to see if the user is a member of one
	 * of the groups passed in.
	 *
	 * @param string ...$groups
	 */
	public function inGroup(string ...$groups): bool
	{
		$this->populateGroups();

		foreach ($groups as $group)
		{
			if (in_array($group, $this->groupCache))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Used internally to populate the User groups
	 * so we hit the database as little as possible.
	 */
	private function populateGroups()
	{
		if (is_array($this->groupCache))
		{
			return;
		}

		$groups = db_connect()->table('auth_groups_users')
			->where('user_id', $this->id)
			->get()
			->getFirstRow('array');

		$this->groupCache = $groups !== null && $groups['groups'] !== null
			? unserialize($groups['groups'])
			: [];
	}

	/**
	 * Used internally to populate the User permissions
	 * so we hit the database as little as possible.
	 */
	private function populatePermissions()
	{
		if (is_array($this->permissionsCache))
		{
			return;
		}

		$permissions = db_connect()->table('auth_permissions_users')
			->where('user_id', $this->id)
			->get()
			->getFirstRow('array');

		$this->permissionsCache = $permissions !== null && $permissions['permissions'] !== null
			? unserialize($permissions['permissions'])
			: [];
	}

	/**
	 * Inserts or Updates either the current groups
	 * or the current permissions.
	 *
	 * @param string $type
	 *
	 * @throws \Exception
	 */
	private function saveGroupsOrPermissions(string $type)
	{
		$table = $type === 'groups'
			? 'auth_groups_users'
			: 'auth_permissions_users';
		$cache = $type === 'groups'
			? $this->groupCache
			: $this->permissionsCache;

		$record = db_connect()->table($table)
			->where('user_id', $this->id)
			->get()
			->getFirstRow('array');

		if (is_array($record))
		{
			db_connect()->table($table)
				->where('id', $record['id'])
				->update([$type => serialize($cache)]);
		}
		else
		{
			db_connect()->table($table)
				->insert([
					'user_id'    => $this->id,
					$type        => serialize($cache),
					'created_at' => Time::now()->toDateTimeString(),
				]);
		}
	}
}
