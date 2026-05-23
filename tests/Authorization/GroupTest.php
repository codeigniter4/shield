<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Authorization;

use CodeIgniter\Shield\Authorization\Groups;
use CodeIgniter\Test\DatabaseTestTrait;
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

    public function testPermissions(): void
    {
        $group = $this->groups->info('admin');

        $permissions = $group->permissions();

        $this->assertIsArray($permissions);
        $this->assertContains('users.create', $permissions);
    }

    public function testSavePermissions(): void
    {
        $group = $this->groups->info('admin');

        $permissions = $group->permissions();
        unset($permissions[array_search('users.create', $permissions, true)]);

        $group->setPermissions($permissions);
        $permissions = $group->permissions();

        $this->assertNotContains('users.create', $permissions);
    }

    public function testAddPermission(): void
    {
        $group = $this->groups->info('admin');

        $group->addPermission('foo.bar');

        $permissions = $group->permissions();
        $this->assertContains('foo.bar', $permissions);
    }

    public function testRemovePermission(): void
    {
        $group = $this->groups->info('admin');

        $group->removePermission('users.edit');

        $permissions = $group->permissions();
        $this->assertNotContains('users.edit', $permissions);
    }

    public function testCan(): void
    {
        $group1 = $this->groups->info('superadmin');
        $group2 = $this->groups->info('admin');

        $this->assertTrue($group1->can('users.*'));
        $this->assertTrue($group2->can('users.edit'));
        $this->assertFalse($group2->can('Users.Edit'));
        $this->assertFalse($group2->can('foo.bar'));
    }

    public function testCanWithHierarchicalWildcards(): void
    {
        $group = $this->groups->info('user');
        $group->addPermission('forum.posts.*');
        $group->addPermission('admin.*.create');

        $this->assertTrue($group->can('forum.posts.create'));
        $this->assertTrue($group->can('forum.posts.comments.delete'));
        $this->assertTrue($group->can('admin.users.create'));
        $this->assertTrue($group->can('forum.posts'));

        $this->assertFalse($group->can('admin.create'));
        $this->assertFalse($group->can('admin.users.roles.create'));
        $this->assertFalse($group->can('admin.users.delete'));
        $this->assertFalse($group->can('forum.users.create'));
    }
}
