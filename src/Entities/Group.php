<?php

namespace Sparks\Shield\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Represents a single User Group
 * and provides utility functions.
 */
class Group extends Entity
{
    /**
     * @var array
     */
    protected $permissions;

    /**
     * Returns the permissions for this group.
     *
     * @return array
     */
    public function permissions()
    {
        $this->populatePermissions();

        return $this->permissions;
    }

    /**
     * Overrides and saves all permissions in the class
     * with the permissions array that is passed in.
     *
     * @return mixed
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        $matrix = setting('AuthGroups.matrix');

        $matrix[$this->alias] = $permissions;

        return setting('AuthGroups.matrix', $matrix);
    }

    /**
     * Adds a single permission to this group and saves it.
     *
     * @return mixed
     */
    public function addPermission(string $permission)
    {
        $this->populatePermissions();

        array_unshift($this->permissions, $permission);

        return $this->setPermissions($this->permissions);
    }

    /**
     * Removes a single permission from this group and saves it.
     *
     * @return mixed
     */
    public function removePermission(string $permission)
    {
        $this->populatePermissions();

        unset($this->permissions[array_search($permission, $this->permissions, true)]);

        return $this->setPermissions($this->permissions);
    }

    /**
     * Determines if the group has the given permission
     */
    public function can(string $permission): bool
    {
        $this->populatePermissions();

        return in_array(strtolower($permission), $this->permissions, true);
    }

    /**
     * Loads our permissions for this group.
     */
    private function populatePermissions()
    {
        if ($this->permissions !== null) {
            return;
        }

        $this->permissions = setting('AuthGroups.matrix')[$this->alias] ?? [];
    }
}
