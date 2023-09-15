<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\PhpStreamWrapper;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class UserTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(CodeIgniter::CI_VERSION, '4.3.0', '>=')) {
            CITestStreamFilter::registration();
            CITestStreamFilter::addOutputFilter();
        } else {
            $this->markTestSkipped('Cannot write tests with CI 4.2.x');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CITestStreamFilter::removeOutputFilter();
        CITestStreamFilter::removeErrorFilter();
    }

    private function setUserInput(string $input): void
    {
        // Register the PhpStreamWrapper.
        PhpStreamWrapper::register();

        PhpStreamWrapper::setContent($input);
    }

    private function resetUserInput(): void
    {
        // Restore php protocol wrapper.
        PhpStreamWrapper::restore();
    }

    public function testCreate(): void
    {
        $input = 'Secret Passw0rd!';
        $this->setUserInput($input);

        command('shield:user create -n user1 -e user1@example.com');

        $this->resetUserInput();

        $this->assertStringContainsString(
            'User user1 created',
            CITestStreamFilter::$buffer
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user1@example.com']);
        $this->seeInDatabase($this->tables['identities'], [
            'user_id' => $user->id,
            'secret'  => 'user1@example.com',
        ]);
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $user->id,
            'active' => 0,
        ]);
    }

    public function testActivate(): void
    {
        // Create a user.
        /** @var User $user */
        $user = fake(UserModel::class, ['username' => 'user2']);
        $user->createEmailIdentity([
            'email'    => 'user2@example.com',
            'password' => 'secret123',
        ]);

        $input = 'y';
        $this->setUserInput($input);

        command('shield:user activate -n user2');

        $this->resetUserInput();

        $this->assertStringContainsString(
            'User user2 activated',
            CITestStreamFilter::$buffer
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user2@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $user->id,
            'active' => 1,
        ]);
    }

    public function testDeactivate(): void
    {
        // Create a user.
        /** @var User $user */
        $user = fake(UserModel::class, ['username' => 'user3', 'active' => 1]);
        $user->createEmailIdentity([
            'email'    => 'user3@example.com',
            'password' => 'secret123',
        ]);

        $input = 'y';
        $this->setUserInput($input);

        command('shield:user deactivate -n user3');

        $this->resetUserInput();

        $this->assertStringContainsString(
            'User user3 deactivated',
            CITestStreamFilter::$buffer
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user3@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $user->id,
            'active' => 0,
        ]);
    }
}
