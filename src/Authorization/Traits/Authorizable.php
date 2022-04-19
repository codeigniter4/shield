<?php

namespace CodeIgniter\Shield\Authorization\Traits;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authorization\AuthorizationException;
use Exception;

trait Authorizable
{
    protected ?array $groupCache       = null;
    protected ?array $permissionsCache = null;

    /**
     * Adds one or more groups to the current User.
     *
     * @return $this
     */
    public function addGroup(string ...$groups)
    {
        $this->populateGroups();
        $configGroups = function_exists('setting')
            ? array_keys(setting('AuthGroups.groups'))
            : array_keys(config('AuthGroups')->groups);

        $groupCount = count($this->groupCache);

        foreach ($groups as $group) {
            $group = strtolower($group);

            // don't allow dupes
            if (in_array($group, $this->groupCache, true)) {
                continue;
            }

            // make sure it's a valid group
            if (! in_array($group, $configGroups, true)) {
                throw AuthorizationException::forUnknownGroup($group);
            }

            $this->groupCache[] = $group;
        }

        // Only save the results if there's anything new.
        if (count($this->groupCache) > $groupCount) {
            $this->saveGroupsOrPermissions('group');
        }

        return $this;
    }

    /**
     * Removes one or more groups from the user.
     *
     * @return $this
     */
    public function removeGroup(string ...$groups)
    {
        $this->populateGroups();

        foreach ($groups as &$group) {
            $group = strtolower($group);
        }

        // Remove from local cache
        $this->groupCache = array_diff($this->groupCache, $groups);

        // Update the database.
        $this->saveGroupsOrPermissions('group');

        return $this;
    }

    /**
     * Given an array of groups, will update the database
     * so only those groups are valid for this user, removing
     * all groups not in this list.
     *
     * @throws AuthorizationException
     *
     * @return $this
     */
    public function syncGroups(array $groups)
    {
        $this->populateGroups();
        $configGroups = function_exists('setting')
            ? array_keys(setting('AuthGroups.groups'))
            : array_keys(config('AuthGroups')->groups);

        foreach ($groups as $group) {
            if (! in_array($group, $configGroups, true)) {
                throw AuthorizationException::forUnknownGroup($group);
            }
        }

        $this->groupCache = $groups;
        $this->saveGroupsOrPermissions('group');

        return $this;
    }

    /**
     * Returns all groups this user is a part of.
     */
    public function getGroups(): ?array
    {
        $this->populateGroups();

        return $this->groupCache;
    }

    /**
     * Returns all permissions this user has
     * assigned directly to them.
     */
    public function getPermissions(): ?array
    {
        $this->populatePermissions();

        return $this->permissionsCache;
    }

    /**
     * Adds one or more permissions to the current user.
     *
     * @throws AuthorizationException
     *
     * @return $this
     */
    public function addPermission(string ...$permissions)
    {
        $this->populatePermissions();
        $configPermissions = function_exists('setting')
            ? array_keys(setting('AuthGroups.permissions'))
            : array_keys(config('AuthGroups')->permissions);

        $permissionCount = count($this->permissionsCache);

        foreach ($permissions as $permission) {
            $permission = strtolower($permission);

            // don't allow dupes
            if (in_array($permission, $this->permissionsCache, true)) {
                continue;
            }

            // make sure it's a valid group
            if (! in_array($permission, $configPermissions, true)) {
                throw AuthorizationException::forUnknownPermission($permission);
            }

            $this->permissionsCache[] = $permission;
        }

        // Only save the results if there's anything new.
        if (count($this->permissionsCache) > $permissionCount) {
            $this->saveGroupsOrPermissions('permission');
        }

        return $this;
    }

    /**
     * Removes one or more permissions from the current user.
     *
     * @return $this
     */
    public function removePermission(string ...$permissions)
    {
        $this->populatePermissions();

        foreach ($permissions as &$permission) {
            $permission = strtolower($permission);
        }

        // Remove from local cache
        $this->permissionsCache = array_diff($this->permissionsCache, $permissions);

        // Update the database.
        $this->saveGroupsOrPermissions('permission');

        return $this;
    }

    /**
     * Given an array of permissions, will update the database
     * so only those permissions are valid for this user, removing
     * all permissions not in this list.
     *
     * @throws AuthorizationException
     *
     * @return $this
     */
    public function syncPermissions(array $permissions)
    {
        $this->populatePermissions();
        $configPermissions = function_exists('setting')
            ? array_keys(setting('AuthGroups.permissions'))
            : array_keys(config('AuthGroups')->permissions);

        foreach ($permissions as $permission) {
            if (! in_array($permission, $configPermissions, true)) {
                throw AuthorizationException::forUnknownPermission($permission);
            }
        }

        $this->permissionsCache = $permissions;
        $this->saveGroupsOrPermissions('permission');

        return $this;
    }

    /**
     * Checks to see if the user has the permission set
     * directly on themselves. This disregards any groups
     * they are part of.
     */
    public function hasPermission(string $permission): bool
    {
        $this->populatePermissions();
        $permission = strtolower($permission);

        return in_array($permission, $this->permissionsCache, true);
    }

    /**
     * Checks user permissions and their group permissions
     * to see if the user has a specific permission.
     */
    public function can(string $permission): bool
    {
        $this->populatePermissions();
        $permission = strtolower($permission);

        // Check user's permissions
        if (in_array($permission, $this->permissionsCache, true)) {
            return true;
        }

        // Check the groups the user belongs to
        $this->populateGroups();

        if (! count($this->groupCache)) {
            return false;
        }

        $matrix = function_exists('setting')
            ? setting('AuthGroups.matrix')
            : config('AuthGroups')->matrix;

        foreach ($this->groupCache as $group) {
            // Check exact match
            if (isset($matrix[$group]) && in_array($permission, $matrix[$group], true)) {
                return true;
            }

            // Check wildcard match
            $check = substr($permission, 0, strpos($permission, '.')) . '.*';
            if (isset($matrix[$group]) && in_array($check, $matrix[$group], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks to see if the user is a member of one
     * of the groups passed in.
     */
    public function inGroup(string ...$groups): bool
    {
        $this->populateGroups();

        foreach ($groups as $group) {
            if (in_array(strtolower($group), $this->groupCache, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Used internally to populate the User groups
     * so we hit the database as little as possible.
     */
    private function populateGroups(): void
    {
        if (is_array($this->groupCache)) {
            return;
        }

        $groups = db_connect()->table('auth_groups_users')
            ->where('user_id', $this->id)
            ->get()
            ->getResultArray();

        $this->groupCache = array_column($groups, 'group');
    }

    /**
     * Used internally to populate the User permissions
     * so we hit the database as little as possible.
     */
    private function populatePermissions(): void
    {
        if (is_array($this->permissionsCache)) {
            return;
        }

        $permissions = db_connect()->table('auth_permissions_users')
            ->where('user_id', $this->id)
            ->get()
            ->getResultArray();

        $this->permissionsCache = array_column($permissions, 'permission');
    }

    /**
     * Inserts or Updates either the current groups
     * or the current permissions.
     *
     * @throws Exception
     */
    private function saveGroupsOrPermissions(string $type): void
    {
        $table = $type === 'group'
            ? 'auth_groups_users'
            : 'auth_permissions_users';
        $cache = $type === 'group'
            ? $this->groupCache
            : $this->permissionsCache;

        $existing = db_connect()->table($table)
            ->where('user_id', $this->id)
            ->get()
            ->getResultArray();
        $existing = array_column($existing, $type);

        $new = array_diff($cache, $existing);

        // Delete any not in the cache
        if (count($cache)) {
            db_connect()->table($table)
                ->where('user_id', $this->id)
                ->whereNotIn($type, $cache)
                ->delete();
        }
        // Nothing in the cache? Then make sure
        // we delete all from this user
        else {
            db_connect()->table($table)
                ->where('user_id', $this->id)
                ->delete();
        }

        // Insert new ones
        if ($new !== []) {
            $inserts = [];

            foreach ($new as $item) {
                $inserts[] = [
                    'user_id'    => $this->id,
                    $type        => $item,
                    'created_at' => Time::now()->toDateTimeString(),
                ];
            }
            db_connect()->table($table)->insertBatch($inserts);
        }
    }
}
