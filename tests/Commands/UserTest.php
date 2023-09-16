<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\Shield\Commands\User;
use CodeIgniter\Shield\Commands\Utils\InputOutput;
use CodeIgniter\Shield\Entities\User as UserEntity;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Commands\Utils\MockInputOutput;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class UserTest extends DatabaseTestCase
{
    private ?InputOutput $io = null;

    protected function tearDown(): void
    {
        parent::tearDown();

        User::resetInputOutput();
    }

    /**
     * Set MockInputOutput and user inputs.
     *
     * @param array<int, string> $inputs User inputs
     * @phpstan-param list<string> $inputs
     */
    private function setMockIo(array $inputs): void
    {
        $this->io = new MockInputOutput();
        $this->io->setInputs($inputs);
        User::setInputOutput($this->io);
    }

    public function testCreate(): void
    {
        $this->setMockIo([
            'Secret Passw0rd!',
            'Secret Passw0rd!',
        ]);

        command('shield:user create -n user1 -e user1@example.com');

        $this->assertStringContainsString(
            'User user1 created',
            $this->io->getLastOutput()
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

    private function createUser(array $userData): UserEntity
    {
        /** @var UserEntity $user */
        $user = fake(UserModel::class, ['username' => $userData['username']]);
        $user->createEmailIdentity([
            'email'    => $userData['email'],
            'password' => $userData['password'],
        ]);

        return $user;
    }

    public function testActivate(): void
    {
        $user = $this->createUser([
            'username' => 'user2',
            'email'    => 'user2@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user activate -n user2');

        $this->assertStringContainsString(
            'User user2 activated',
            $this->io->getLastOutput()
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
        $user = $this->createUser([
            'username' => 'user3',
            'email'    => 'user3@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user deactivate -n user3');

        $this->assertStringContainsString(
            'User user3 deactivated',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user3@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $user->id,
            'active' => 0,
        ]);
    }
}
