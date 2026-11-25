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

namespace CodeIgniter\Shield\Tests\Authentication\Authenticators;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Authentication\Authenticators\LDAP;
use CodeIgniter\Shield\Authentication\LDAP\LDAPConnection;
use CodeIgniter\Shield\Authentication\LDAP\LDAPDirectoryInterface;
use CodeIgniter\Shield\Authentication\LDAP\LDAPUserResolver;
use CodeIgniter\Shield\Config\AuthLDAP;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class LDAPAuthenticatorTest extends TestCase
{
    private const CREDENTIALS = ['username' => 'directory-user', 'password' => 'test-password'];
    private const ENTRY       = ['uid' => ['directory-user'], 'employeenumber' => ['immutable-id']];

    private LDAPDirectoryInterface&MockObject $directory;
    private MockObject&UserModel $users;
    private MockObject&UserIdentityModel $identities;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory  = $this->createMock(LDAPDirectoryInterface::class);
        $this->users      = $this->createMock(UserModel::class);
        $this->identities = $this->createMock(UserIdentityModel::class);
    }

    #[DataProvider('provideRejectsInvalidCredentialsBeforeDirectoryLookup')]
    public function testRejectsInvalidCredentialsBeforeDirectoryLookup(string $field, string $value): void
    {
        $this->directory->expects($this->never())->method('authenticate');
        $this->users->expects($this->never())->method('builder');
        $result = $this->authenticator()->check([$field => $value] + self::CREDENTIALS);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badAttempt'), $result->reason());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideRejectsInvalidCredentialsBeforeDirectoryLookup(): iterable
    {
        yield 'long identifier' => ['username', str_repeat('a', 256)];

        yield 'control character in identifier' => ['username', "directory\nuser"];

        yield 'long password' => ['password', str_repeat('p', 4097)];

        yield 'NUL in password' => ['password', "pass\0word"];
    }

    #[DataProvider('provideRejectsMultipleValuesForSingleValuedAttributes')]
    public function testRejectsMultipleValuesForSingleValuedAttributes(string $attribute, string $reason): void
    {
        $this->mockDirectoryEntry([$attribute => ['first', 'second']] + self::ENTRY);
        $this->users->expects($this->never())->method('builder');
        $result = $this->authenticator()->check(self::CREDENTIALS);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badAttempt'), $result->reason());
        $this->assertLogged('debug', "Shield LDAP login rejected ({$reason}).");
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideRejectsMultipleValuesForSingleValuedAttributes(): iterable
    {
        yield 'external ID' => ['employeenumber', 'invalid_identity'];

        yield 'mapped profile field' => ['uid', 'invalid_profile'];
    }

    #[DataProvider('provideRejectsIneligibleLinkedUsers')]
    public function testRejectsIneligibleLinkedUsers(bool $banned): void
    {
        setting('Auth.actions', ['register' => EmailActivator::class, 'login' => null]);
        $user = new User([
            'id'       => 42,
            'username' => 'existing-user',
            'status'   => $banned ? 'banned' : null,
            'active'   => $banned,
        ]);

        $this->mockDirectoryEntry(self::ENTRY);
        $this->mockIdentityLookup($user);
        $this->users->expects($this->never())->method('update');
        $result = $this->authenticator()->check(self::CREDENTIALS);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badAttempt'), $result->reason());
    }

    /**
     * @return iterable<string, array{bool}>
     */
    public static function provideRejectsIneligibleLinkedUsers(): iterable
    {
        yield 'banned user' => [true];

        yield 'unactivated user' => [false];
    }

    public function testRejectsUnlinkedUserWhenProvisioningIsDisabled(): void
    {
        $this->mockDirectoryEntry(self::ENTRY);
        $this->mockIdentityLookup(null);
        $this->users->expects($this->never())->method('insert');
        $this->identities->expects($this->never())->method('create');
        $result = $this->authenticator(provisionUsers: false)->check(self::CREDENTIALS);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.badAttempt'), $result->reason());
        $this->assertLogged('debug', 'Shield LDAP login rejected (user_not_provisioned).');
    }

    public function testReturnsLinkedUserWithoutWritingWhenProfileUpdatesAreDisabled(): void
    {
        $user = new User(['id' => 42, 'username' => 'existing-user']);
        $this->mockDirectoryEntry(self::ENTRY);
        $this->mockIdentityLookup($user);
        $this->users->expects($this->never())->method('update');
        $result = $this->authenticator(updateProfile: false)->check(self::CREDENTIALS);

        $this->assertTrue($result->isOK());
        $this->assertSame($user, $result->extraInfo());
        $this->assertSame('existing-user', $user->username);
    }

    private function authenticator(bool $provisionUsers = true, bool $updateProfile = true): LDAP
    {
        $connection = new LDAPConnection(
            id: 'corporate',
            hosts: ['ldap.example.test'],
            baseDn: 'dc=example,dc=test',
            bindDn: 'cn=reader,dc=example,dc=test',
            bindPassword: 'test-service-password',
            externalIdAttribute: 'employeeNumber',
            profileAttributes: ['username' => 'uid'],
            provisionUsers: $provisionUsers,
            updateProfile: $updateProfile,
        );
        $config                    = $this->createPartialMock(AuthLDAP::class, ['getConnection']);
        $config->enabled           = true;
        $config->defaultConnection = $connection->id;
        $config->method('getConnection')->with($connection->id)->willReturn($connection);

        return new LDAP($this->users, $config, $this->directory, new LDAPUserResolver($this->users, $this->identities));
    }

    /**
     * @param array<string, list<string>> $attributes
     */
    private function mockDirectoryEntry(array $attributes): void
    {
        $this->directory->expects($this->once())->method('authenticate')->willReturn([
            'dn'         => 'uid=directory-user,dc=example,dc=test',
            'attributes' => $attributes,
        ]);
    }

    private function mockIdentityLookup(?User $user): void
    {
        $database = $this->createMock(BaseConnection::class);
        $database->method('__get')->willReturnMap([['transDepth', 0], ['transStrict', true]]);
        $database->method('transStatus')->willReturn(true);
        $database->method('transBegin')->willReturn(true);
        $database->method('transComplete')->willReturn(true);

        $builder = $this->createMock(BaseBuilder::class);
        $builder->method('db')->willReturn($database);
        $this->users->method('builder')->willReturn($builder);
        $this->identities->method('builder')->willReturn($builder);

        $identity = $user === null ? null : new UserIdentity(['user_id' => $user->id]);
        $this->identities->expects($this->once())->method('getIdentityBySecret')
            ->with(LDAPUserResolver::ID_TYPE, hash('sha256', "corporate\0immutable-id"))
            ->willReturn($identity);

        if ($user !== null) {
            $this->users->expects($this->once())->method('findById')->with($user->id)->willReturn($user);
        }
    }
}
