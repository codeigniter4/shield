<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authorization\Traits;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authorization\AuthorizationException;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Models\GroupModel;
use CodeIgniter\Shield\Models\PermissionModel;

trait Authorizable
{
    protected ?array $groupCache       = null;
    protected ?array $permissionsCache = null;

    /**
     * Adds one or more groups to the current User.
     *
     * @return $this
     */
    public function addGroup(string ...$groups): self
    {
        $this->populateGroups();

        $configGroups = $this->getConfigGroups();

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
            $this->saveGroups();
        }

        return $this;
    }

    /**
     * Removes one or more groups from the user.
     *
     * @return $this
     */
    public function removeGroup(string ...$groups): self
    {
        $this->populateGroups();

        foreach ($groups as &$group) {
            $group = strtolower($group);
        }

        // Remove from local cache
        $this->groupCache = array_diff($this->groupCache, $groups);

        // Update the database.
        $this->saveGroups();

        return $this;
    }

    /**
     * Given an array of groups, will update the database
     * so only those groups are valid for this user, removing
     * all groups not in this list.
     *
     * @return $this
     *
     * @throws AuthorizationException
     */
    public function syncGroups(string ...$groups): self
    {
        $this->populateGroups();

        $configGroups = $this->getConfigGroups();

        foreach ($groups as $group) {
            if (! in_array($group, $configGroups, true)) {
                throw AuthorizationException::forUnknownGroup($group);
            }
        }

        $this->groupCache = $groups;
        $this->saveGroups();

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
     * @return $this
     *
     * @throws AuthorizationException
     */
    public function addPermission(string ...$permissions): self
    {
        $this->populatePermissions();

        $configPermissions = $this->getConfigPermissions();

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
            $this->savePermissions();
        }

        return $this;
    }

    /**
     * Removes one or more permissions from the current user.
     *
     * @return $this
     */
    public function removePermission(string ...$permissions): self
    {
        $this->populatePermissions();

        foreach ($permissions as &$permission) {
            $permission = strtolower($permission);
        }

        // Remove from local cache
        $this->permissionsCache = array_diff($this->permissionsCache, $permissions);

        // Update the database.
        $this->savePermissions();

        return $this;
    }

    /**
     * Given an array of permissions, will update the database
     * so only those permissions are valid for this user, removing
     * all permissions not in this list.
     *
     * @return $this
     *
     * @throws AuthorizationException
     */
    public function syncPermissions(string ...$permissions): self
    {
        $this->populatePermissions();

        $configPermissions = $this->getConfigPermissions();

        foreach ($permissions as $permission) {
            if (! in_array($permission, $configPermissions, true)) {
                throw AuthorizationException::forUnknownPermission($permission);
            }
        }

        $this->permissionsCache = $permissions;
        $this->savePermissions();

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
     * to see if the user has a specific permission or group
     * of permissions.
     *
     * @param string $permissions string(s) consisting of a scope and action, like `users.create`
     */
    public function can(string ...$permissions): bool
    {
        // Get user's permissions and store in cache
        $this->populatePermissions();

        // Check the groups the user belongs to
        $this->populateGroups();

        foreach ($permissions as $permission) {
            // Permission must contain a scope and action
            if (strpos($permission, '.') === false) {
                throw new LogicException(
                    'A permission must be a string consisting of a scope and action, like `users.create`.'
                    . ' Invalid permission: ' . $permission
                );
            }

            $permission = strtolower($permission);

            // Check user's permissions
            if (in_array($permission, $this->permissionsCache, true)) {
                return true;
            }

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

        /** @var GroupModel $groupModel */
        $groupModel = model(GroupModel::class);

        $this->groupCache = $groupModel->getForUser($this);
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

        /** @var PermissionModel $permissionModel */
        $permissionModel = model(PermissionModel::class);

        $this->permissionsCache = $permissionModel->getForUser($this);
    }

    /**
     * Inserts or Updates the current groups.
     */
    private function saveGroups(): void
    {
        /** @var GroupModel $model */
        $model = model(GroupModel::class);

        $cache = $this->groupCache;

        $this->saveGroupsOrPermissions('group', $model, $cache);
    }

    /**
     * Inserts or Updates either the current permissions.
     */
    private function savePermissions(): void
    {
        /** @var PermissionModel $model */
        $model = model(PermissionModel::class);

        $cache = $this->permissionsCache;

        $this->saveGroupsOrPermissions('permission', $model, $cache);
    }

    /**
     * @phpstan-param 'group'|'permission' $type
     * @param GroupModel|PermissionModel $model
     */
    private function saveGroupsOrPermissions(string $type, $model, array $cache): void
    {
        $existing = $model->getForUser($this);

        $new = array_diff($cache, $existing);

        // Delete any not in the cache
        if ($cache !== []) {
            $model->deleteNotIn($this->id, $cache);
        }
        // Nothing in the cache? Then make sure
        // we delete all from this user
        else {
            $model->deleteAll($this->id);
        }

        // Insert new ones
        if ($new !== []) {
            $inserts = [];

            foreach ($new as $item) {
                $inserts[] = [
                    'user_id'    => $this->id,
                    $type        => $item,
                    'created_at' => Time::now()->format('Y-m-d H:i:s'),
                ];
            }

            $model->insertBatch($inserts);
        }
    }

    /**
     * @return string[]
     */
    private function getConfigGroups(): array
    {
        return function_exists('setting')
            ? array_keys(setting('AuthGroups.groups'))
            : array_keys(config('AuthGroups')->groups);
    }

    /**
     * @return string[]
     */
    private function getConfigPermissions(): array
    {
        return function_exists('setting')
            ? array_keys(setting('AuthGroups.permissions'))
            : array_keys(config('AuthGroups')->permissions);
    }
}
