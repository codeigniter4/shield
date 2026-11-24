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

namespace CodeIgniter\Shield\Tests\Unit;

use CodeIgniter\Shield\Config\AuthLDAP;
use CodeIgniter\Shield\Exceptions\LDAPException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 */
final class AuthLDAPTest extends TestCase
{
    private const CONNECTION_METADATA = [
        'hosts'               => ['ldap.example.test', 'ldap-backup.example.test'],
        'baseDn'              => 'dc=example,dc=test',
        'bindDn'              => 'cn=reader,dc=example,dc=test',
        'bindPassword'        => 'metadata-test-password',
        'encryption'          => 'starttls',
        'port'                => 1389,
        'connectTimeout'      => 4,
        'searchTimeout'       => 8,
        'userBaseDn'          => 'ou=users,dc=example,dc=test',
        'loginFilter'         => '(objectClass=inetOrgPerson)',
        'loginAttributes'     => ['cn', 'mail'],
        'externalIdAttribute' => 'objectGUID',
        'profileAttributes'   => ['email' => 'mail'],
        'provisionUsers'      => true,
        'eligibilityFilter'   => '(accountStatus=active)',
        'updateProfile'       => false,
    ];

    public function testDefaultConfigurationHasNoConnections(): void
    {
        $this->assertSame([], (new AuthLDAP())->connectionIds());
    }

    public function testBuildsConnectionFromConfiguredMetadata(): void
    {
        $metadata = self::CONNECTION_METADATA;
        unset($metadata['bindPassword']);

        $config              = new AuthLDAP();
        $config->connections = [
            'missing-bind-secret-test' => $metadata,
            'corporate'                => self::CONNECTION_METADATA,
        ];

        $this->assertSame(['missing-bind-secret-test', 'corporate'], $config->connectionIds());

        $connection = $config->getConnection('corporate');

        $this->assertSame('corporate', $connection->id);
        $this->assertSame($metadata, array_intersect_key(get_object_vars($connection), $metadata));
        $this->assertSame('metadata-test-password', $connection->bindPassword());
    }

    #[DataProvider('provideLoadsNestedEnvironmentOverrides')]
    public function testLoadsNestedEnvironmentOverrides(string $separator): void
    {
        $overrides = [
            'authldap.connections.corporate.bindPassword'   => 'environment-test-password',
            'authldap.connections.corporate.hosts.0'        => 'ldap-env.example.test',
            'authldap.connections.corporate.port'           => '1636',
            'authldap.connections.corporate.provisionUsers' => 'false',
        ];

        $original = [];

        try {
            foreach ($overrides as $name => $value) {
                $key            = str_replace('.', $separator, $name);
                $original[$key] = getenv($key);
                putenv($key . '=' . $value);
            }

            // Seed defaults before BaseConfig loads the nested environment overrides.
            $reflection          = new ReflectionClass(AuthLDAP::class);
            $config              = $reflection->newInstanceWithoutConstructor();
            $config->connections = ['corporate' => self::CONNECTION_METADATA];
            $reflection->getConstructor()?->invoke($config);

            $connection = $config->getConnection('corporate');

            $this->assertSame('environment-test-password', $connection->bindPassword());
            $this->assertSame(['ldap-env.example.test', 'ldap-backup.example.test'], $connection->hosts);
            $this->assertSame(1636, $connection->port);
            $this->assertFalse($connection->provisionUsers);
        } finally {
            foreach ($original as $key => $value) {
                putenv($value === false ? $key : $key . '=' . $value);
            }
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideLoadsNestedEnvironmentOverrides(): iterable
    {
        yield 'dotted' => ['.'];

        yield 'underscored' => ['_'];
    }

    public function testRedactsBindPasswordFromDiagnosticOutput(): void
    {
        $config              = new AuthLDAP();
        $config->connections = ['corporate' => self::CONNECTION_METADATA];
        $connection          = $config->getConnection('corporate');

        $this->assertStringNotContainsString('metadata-test-password', print_r($config, true));
        $this->assertStringNotContainsString('metadata-test-password', var_export($connection, true));
    }

    /**
     * @param array<string, mixed> $settings
     */
    #[DataProvider('provideRejectsMissingOrEmptyBindPassword')]
    public function testRejectsMissingOrEmptyBindPassword(array $settings): void
    {
        $config              = new AuthLDAP();
        $config->connections = ['missing-bind-secret-test' => $settings];

        $this->assertSame(['missing-bind-secret-test'], $config->connectionIds());

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('invalid_bind_secret');

        $config->getConnection('missing-bind-secret-test');
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function provideRejectsMissingOrEmptyBindPassword(): iterable
    {
        yield 'missing' => [array_diff_key(self::CONNECTION_METADATA, ['bindPassword' => true])];

        yield 'empty' => [['bindPassword' => ''] + self::CONNECTION_METADATA];
    }

    /**
     * @param array<array-key, mixed> $options
     */
    #[DataProvider('provideRejectsInvalidConnectionOptions')]
    public function testRejectsInvalidConnectionOptions(array $options): void
    {
        $config              = new AuthLDAP();
        $config->connections = ['corporate' => $options + self::CONNECTION_METADATA];

        try {
            $config->getConnection('corporate');
            $this->fail('Invalid connection options must be rejected.');
        } catch (LDAPException $exception) {
            $this->assertSame('invalid_configuration', $exception->reason);
            $this->assertStringNotContainsString('metadata-test-password', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
        }
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function provideRejectsInvalidConnectionOptions(): iterable
    {
        yield 'nested connection ID' => [['id' => 'invalid-inline-id']];

        yield 'invalid port type' => [['port' => 'not-a-port']];

        yield 'numeric host' => [['hosts' => [123]]];

        yield 'positional option' => [['starttls']];
    }

    public function testRejectsUnknownConnectionId(): void
    {
        $config              = new AuthLDAP();
        $config->connections = ['corporate' => self::CONNECTION_METADATA];

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('invalid_configuration');

        $config->getConnection('unknown');
    }
}
