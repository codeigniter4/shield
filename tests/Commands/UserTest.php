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

namespace Tests\Commands;

use CodeIgniter\Shield\Commands\User;
use CodeIgniter\Shield\Config\AuthGroups;
use CodeIgniter\Shield\Entities\User as UserEntity;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Test\MockInputOutput;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class UserTest extends DatabaseTestCase
{
    private ?MockInputOutput $io = null;

    protected function tearDown(): void
    {
        parent::tearDown();

        User::resetInputOutput();
    }

    /**
     * Set MockInputOutput and user inputs.
     *
     * @param         array<int, string> $inputs User inputs
     * @phpstan-param list<string>       $inputs
     */
    private function setMockIo(array $inputs): void
    {
        $this->io = new MockInputOutput();
        $this->io->setInputs($inputs);
        User::setInputOutput($this->io);
    }

    private function getOutputWithoutColorCode(): string
    {
        $output = str_replace(["\033[0;32m", "\033[0m"], '', $this->io->getOutputs());

        if (is_windows()) {
            $output = str_replace("\r\n", "\n", $output);
        }

        return $output;
    }

    public function testNoAction(): void
    {
        $this->setMockIo([]);

        command('shield:user');

        $this->assertStringContainsString(
            'Specify a valid action: create,activate,deactivate,changename,changeemail,delete,password,list,addgroup,removegroup',
            $this->io->getLastOutput()
        );
    }

    public function testCreate(): void
    {
        $this->setMockIo([
            'Secret Passw0rd!',
            'Secret Passw0rd!',
        ]);

        command('shield:user create -n user1 -e user1@example.com');

        $this->assertStringContainsString(
            'User "user1" created',
            $this->io->getFirstOutput()
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
        $this->seeInDatabase($this->tables['groups_users'], [
            'user_id' => $user->id,
            'group'   => config(AuthGroups::class)->defaultGroup,
        ]);
    }

    public function testCreateNotUniqueName(): void
    {
        $user = $this->createUser([
            'username' => 'user1',
            'email'    => 'user1@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo([
            'Secret Passw0rd!',
            'Secret Passw0rd!',
        ]);

        command('shield:user create -n user1 -e userx@example.com');

        $this->assertStringContainsString(
            'The Username field must contain a unique value.',
            $this->io->getFirstOutput()
        );
        $this->assertStringContainsString(
            'User creation aborted',
            $this->io->getFirstOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'userx@example.com']);
        $this->assertNull($user);
    }

    public function testCreatePasswordNotMatch(): void
    {
        $user = $this->createUser([
            'username' => 'user1',
            'email'    => 'user1@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo([
            'password',
            'badpassword',
        ]);

        command('shield:user create -n user1 -e userx@example.com');

        $this->assertStringContainsString(
            "The passwords don't match",
            $this->io->getFirstOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'userx@example.com']);
        $this->assertNull($user);
    }

    /**
     * Create an active user.
     */
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

        $user->deactivate();
        $users = model(UserModel::class);
        $users->save($user);

        $this->setMockIo(['y']);

        command('shield:user activate -n user2');

        $this->assertStringContainsString(
            'User "user2" activated',
            $this->io->getLastOutput()
        );

        $user = $users->findByCredentials(['email' => 'user2@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $user->id,
            'active' => 1,
        ]);
    }

    public function testDeactivate(): void
    {
        $this->createUser([
            'username' => 'user3',
            'email'    => 'user3@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user deactivate -n user3');

        $this->assertStringContainsString(
            'User "user3" deactivated',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user3@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $user->id,
            'active' => 0,
        ]);
    }

    public function testChangename(): void
    {
        $this->createUser([
            'username' => 'user4',
            'email'    => 'user4@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user changename -n user4 --new-name newuser4');

        $this->assertStringContainsString(
            'Username "user4" changed to "newuser4"',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user4@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'       => $user->id,
            'username' => 'newuser4',
        ]);
    }

    public function testChangenameInvalidName(): void
    {
        $this->createUser([
            'username' => 'user4',
            'email'    => 'user4@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user changename -n user4 --new-name 1');

        $this->assertStringContainsString(
            'The Username field must be at least 3 characters in length.',
            $this->io->getFirstOutput()
        );
        $this->assertStringContainsString(
            'User name change aborted',
            $this->io->getFirstOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user4@example.com']);
        $this->seeInDatabase($this->tables['users'], [
            'id'       => $user->id,
            'username' => 'user4',
        ]);
    }

    public function testChangeemail(): void
    {
        $this->createUser([
            'username' => 'user5',
            'email'    => 'user5@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user changeemail -n user5 --new-email newuser5@example.jp');

        $this->assertStringContainsString(
            'Email for "user5" changed to newuser5@example.jp',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'newuser5@example.jp']);
        $this->seeInDatabase($this->tables['users'], [
            'id'       => $user->id,
            'username' => 'user5',
        ]);
    }

    public function testChangeemailInvalidEmail(): void
    {
        $this->createUser([
            'username' => 'user5',
            'email'    => 'user5@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user changeemail -n user5 --new-email invalid');

        $this->assertStringContainsString(
            'The Email Address field must contain a valid email address.',
            $this->io->getFirstOutput()
        );
        $this->assertStringContainsString(
            'User email change aborted',
            $this->io->getFirstOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'invalid']);
        $this->assertNull($user);
    }

    public function testDelete(): void
    {
        $this->createUser([
            'username' => 'user6',
            'email'    => 'user6@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user delete -n user6');

        $this->assertStringContainsString(
            'User "user6" deleted',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user6@example.com']);
        $this->assertNull($user);
    }

    public function testDeleteById(): void
    {
        $user = $this->createUser([
            'username' => 'user6',
            'email'    => 'user6@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user delete -i ' . $user->id);

        $this->assertStringContainsString(
            'User "user6" deleted',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user6@example.com']);
        $this->assertNull($user);
    }

    public function testDeleteUserNotExist(): void
    {
        $this->createUser([
            'username' => 'user6',
            'email'    => 'user6@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user delete -n userx');

        $this->assertStringContainsString(
            "User doesn't exist",
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user6@example.com']);
        $this->assertNotNull($user);
    }

    public function testPassword(): void
    {
        $this->createUser([
            'username' => 'user7',
            'email'    => 'user7@example.com',
            'password' => 'secret123',
        ]);
        $users           = model(UserModel::class);
        $user            = $users->findByCredentials(['email' => 'user7@example.com']);
        $oldPasswordHash = $user->password_hash;

        $this->setMockIo(['y', 'newpassword', 'newpassword']);

        command('shield:user password -n user7');

        $this->assertStringContainsString(
            'Password for "user7" set',
            $this->io->getLastOutput()
        );

        $user = $users->findByCredentials(['email' => 'user7@example.com']);
        $this->assertNotSame($oldPasswordHash, $user->password_hash);
    }

    public function testPasswordWithoutOptionsAndSpecifyEmail(): void
    {
        $this->createUser([
            'username' => 'user7',
            'email'    => 'user7@example.com',
            'password' => 'secret123',
        ]);
        $users           = model(UserModel::class);
        $user            = $users->findByCredentials(['email' => 'user7@example.com']);
        $oldPasswordHash = $user->password_hash;

        $this->setMockIo([
            'e', 'user7@example.com', 'y', 'newpassword', 'newpassword',
        ]);

        command('shield:user password');

        $this->assertStringContainsString(
            'Password for "user7" set',
            $this->io->getLastOutput()
        );

        $user = $users->findByCredentials(['email' => 'user7@example.com']);
        $this->assertNotSame($oldPasswordHash, $user->password_hash);
    }

    public function testPasswordNotMatch(): void
    {
        $this->createUser([
            'username' => 'user7',
            'email'    => 'user7@example.com',
            'password' => 'secret123',
        ]);
        $users           = model(UserModel::class);
        $user            = $users->findByCredentials(['email' => 'user7@example.com']);
        $oldPasswordHash = $user->password_hash;

        $this->setMockIo([
            'u', 'user7', 'y', 'newpassword', 'badpassword',
        ]);

        command('shield:user password');

        $this->assertStringContainsString(
            "The passwords don't match",
            $this->io->getLastOutput()
        );

        $user = $users->findByCredentials(['email' => 'user7@example.com']);
        $this->assertSame($oldPasswordHash, $user->password_hash);
    }

    public function testList(): void
    {
        $this->createUser([
            'username' => 'user8',
            'email'    => 'user8@example.com',
            'password' => 'secret123',
        ]);
        $this->createUser([
            'username' => 'user9',
            'email'    => 'user9@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo([]);

        command('shield:user list');

        $this->assertStringContainsString(
            'Id	User
1	user8 (user8@example.com)
2	user9 (user9@example.com)
',
            $this->getOutputWithoutColorCode()
        );
    }

    public function testListByEmail(): void
    {
        $this->createUser([
            'username' => 'user8',
            'email'    => 'user8@example.com',
            'password' => 'secret123',
        ]);
        $this->createUser([
            'username' => 'user9',
            'email'    => 'user9@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo([]);

        command('shield:user list -e user9@example.com');

        $this->assertStringContainsString(
            'Id	User
2	user9 (user9@example.com)
',
            $this->getOutputWithoutColorCode()
        );
    }

    public function testAddgroup(): void
    {
        $this->createUser([
            'username' => 'user10',
            'email'    => 'user10@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['y']);

        command('shield:user addgroup -n user10 -g admin');

        $this->assertStringContainsString(
            'User "user10" added to group "admin"',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user10@example.com']);
        $this->assertTrue($user->inGroup('admin'));
    }

    public function testAddgroupCancel(): void
    {
        $this->createUser([
            'username' => 'user10',
            'email'    => 'user10@example.com',
            'password' => 'secret123',
        ]);

        $this->setMockIo(['n']);

        command('shield:user addgroup -n user10 -g admin');

        $this->assertStringContainsString(
            'Addition of the user "user10" to the group "admin" cancelled',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user10@example.com']);
        $this->assertFalse($user->inGroup('admin'));
    }

    public function testRemovegroup(): void
    {
        $this->createUser([
            'username' => 'user11',
            'email'    => 'user11@example.com',
            'password' => 'secret123',
        ]);
        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user11@example.com']);
        $user->addGroup('admin');
        $this->assertTrue($user->inGroup('admin'));

        $this->setMockIo(['y']);

        command('shield:user removegroup -n user11 -g admin');

        $this->assertStringContainsString(
            'User "user11" removed from group "admin"',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user11@example.com']);
        $this->assertFalse($user->inGroup('admin'));
    }

    public function testRemovegroupCancel(): void
    {
        $this->createUser([
            'username' => 'user11',
            'email'    => 'user11@example.com',
            'password' => 'secret123',
        ]);
        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user11@example.com']);
        $user->addGroup('admin');
        $this->assertTrue($user->inGroup('admin'));

        $this->setMockIo(['n']);

        command('shield:user removegroup -n user11 -g admin');

        $this->assertStringContainsString(
            'Removal of the user "user11" from the group "admin" cancelled',
            $this->io->getLastOutput()
        );

        $users = model(UserModel::class);
        $user  = $users->findByCredentials(['email' => 'user11@example.com']);
        $this->assertTrue($user->inGroup('admin'));
    }
}
