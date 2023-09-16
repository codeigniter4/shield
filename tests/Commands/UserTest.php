<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\Shield\Commands\User;
use CodeIgniter\Shield\Entities\User as UserEntity;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Commands\Utils\MockInputOutput;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class UserTest extends DatabaseTestCase
{
    public function testCreate(): void
    {
        $io = new MockInputOutput();
        $io->setInputs([
            'Secret Passw0rd!',
            'Secret Passw0rd!',
        ]);
        User::setInputOutput($io);

        command('shield:user create -n user1 -e user1@example.com');

        User::resetInputOutput();

        $this->assertStringContainsString(
            'User user1 created',
            $io->getLastOutput()
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
        /** @var UserEntity $user */
        $user = fake(UserModel::class, ['username' => 'user2']);
        $user->createEmailIdentity([
            'email'    => 'user2@example.com',
            'password' => 'secret123',
        ]);

        $io = new MockInputOutput();
        $io->setInputs([
            'y',
        ]);
        User::setInputOutput($io);

        command('shield:user activate -n user2');

        User::resetInputOutput();

        $this->assertStringContainsString(
            'User user2 activated',
            $io->getLastOutput()
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
        /** @var UserEntity $user */
        $user = fake(UserModel::class, ['username' => 'user3', 'active' => 1]);
        $user->createEmailIdentity([
            'email'    => 'user3@example.com',
            'password' => 'secret123',
        ]);

        $io = new MockInputOutput();
        $io->setInputs([
            'y',
        ]);
        User::setInputOutput($io);

        command('shield:user deactivate -n user3');

        User::resetInputOutput();

        $this->assertStringContainsString(
            'User user3 deactivated',
            $io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user3@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $user->id,
            'active' => 0,
        ]);
    }
}
