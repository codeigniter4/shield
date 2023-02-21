<?php

namespace Tests\Authorization;

use CodeIgniter\Test\DatabaseTestTrait;
use RuntimeException;
use Sparks\Shield\Authorization\Groups;
use Sparks\Shield\Entities\Group;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class GroupsTest extends TestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace;

    /**
     * @var Groups
     */
    protected $groups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groups = new Groups();
    }

    public function testGroupInfoBadName()
    {
        $this->assertNull($this->groups->info('foo'));
    }

    public function testGroupInfo()
    {
        $group = $this->groups->info('admin');

        $this->assertInstanceOf(Group::class, $group);
        $this->assertSame('Admin', $group->title);
        $this->assertSame(setting('AuthGroups.groups')['admin']['description'], $group->description);
        $this->assertSame('admin', $group->alias);
    }

    public function testSaveGroupCreatesNew()
    {
        $group = new Group([
            'alias'       => 'foo',
            'title'       => 'Foo Dog',
            'description' => 'Foo Dog was here',
        ]);

        $this->groups->save($group);

        $this->assertInstanceOf(Group::class, $this->groups->info('foo'));
    }

    public function testSaveGroupCreatesNewMakesAlias()
    {
        $group = new Group([
            'title'       => 'Foo Dog',
            'description' => 'Foo Dog was here',
        ]);

        $this->groups->save($group);

        $this->assertInstanceOf(Group::class, $this->groups->info('foo-dog'));
    }

    public function testSaveGroupThrowsOnMissingTitle()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(lang('Auth.missingTitle'));

        $group = new Group([
            'description' => 'Foo Dog was here',
        ]);

        $this->groups->save($group);
    }
}
