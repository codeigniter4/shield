<?php

namespace Tests\Authorization;

use CodeIgniter\Test\DatabaseTestTrait;
use Sparks\Shield\Authorization\Groups;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class GroupTest extends TestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace;
    protected $groups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groups = new Groups();
    }

    public function testPermissions()
    {
        $group = $this->groups->info('admin');

        $permissions = $group->permissions();

        $this->assertIsArray($permissions);
        $this->assertContains('users.create', $permissions);
    }

    public function testSavePermissions()
    {
        $group = $this->groups->info('admin');

        $permissions = $group->permissions();
        unset($permissions[array_search('users.create', $permissions, true)]);

        $group->setPermissions($permissions);
        $permissions = $group->permissions();

        $this->assertNotContains('users.create', $permissions);
    }

    public function testAddPermission()
    {
        $group = $this->groups->info('admin');

        $group->addPermission('foo.bar');

        $permissions = $group->permissions();
        $this->assertContains('foo.bar', $permissions);
    }

    public function testRemovePermission()
    {
        $group = $this->groups->info('admin');

        $group->removePermission('users.edit');

        $permissions = $group->permissions();
        $this->assertNotContains('users.edit', $permissions);
    }

    public function testCan()
    {
        $group = $this->groups->info('admin');

        $this->assertTrue($group->can('users.edit'));
        $this->assertFalse($group->can('foo.bar'));
    }
}
