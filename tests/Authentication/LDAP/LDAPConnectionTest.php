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

namespace CodeIgniter\Shield\Tests\Authentication\LDAP;

use CodeIgniter\Shield\Authentication\LDAP\LDAPConnection;
use CodeIgniter\Shield\Exceptions\LDAPException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class LDAPConnectionTest extends TestCase
{
    #[DataProvider('provideAcceptsBoundaryValues')]
    public function testAcceptsBoundaryValues(int $port, int $timeout, int $hostCount): void
    {
        $hosts      = array_fill(0, $hostCount, 'ldap.example.test');
        $connection = new LDAPConnection(
            'test',
            $hosts,
            'dc=example,dc=test',
            'cn=reader,dc=example,dc=test',
            'readerPassword!',
            port: $port,
            connectTimeout: $timeout,
            searchTimeout: $timeout,
        );

        $this->assertSame($port, $connection->port);
        $this->assertSame($timeout, $connection->connectTimeout);
        $this->assertSame($timeout, $connection->searchTimeout);
        $this->assertSame($hosts, $connection->hosts);
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function provideAcceptsBoundaryValues(): iterable
    {
        yield 'minimum' => [1, 1, 1];

        yield 'maximum' => [65535, 60, 10];
    }

    #[DataProvider('provideRejectsOutOfRangeConfiguration')]
    public function testRejectsOutOfRangeConfiguration(?int $port, int $connectTimeout, int $searchTimeout, int $hostCount): void
    {
        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('invalid_configuration');

        new LDAPConnection(
            'test',
            array_fill(0, $hostCount, 'ldap.example.test'),
            'dc=example,dc=test',
            'cn=reader,dc=example,dc=test',
            'readerPassword!',
            port: $port,
            connectTimeout: $connectTimeout,
            searchTimeout: $searchTimeout,
        );
    }

    /**
     * @return iterable<string, array{?int, int, int, int}>
     */
    public static function provideRejectsOutOfRangeConfiguration(): iterable
    {
        yield 'port below minimum' => [0, 5, 5, 1];

        yield 'port above maximum' => [65536, 5, 5, 1];

        yield 'connect timeout below minimum' => [null, 0, 5, 1];

        yield 'connect timeout above maximum' => [null, 61, 5, 1];

        yield 'search timeout below minimum' => [null, 5, 0, 1];

        yield 'search timeout above maximum' => [null, 5, 61, 1];

        yield 'empty host list' => [null, 5, 5, 0];

        yield 'too many hosts' => [null, 5, 5, 11];
    }

    #[DataProvider('provideFormatsIPv6Endpoints')]
    public function testFormatsIPv6Endpoints(string $encryption, ?int $port, string $expected): void
    {
        $host       = '2001:db8::1';
        $connection = new LDAPConnection(
            'test',
            [$host],
            'dc=example,dc=test',
            'cn=reader,dc=example,dc=test',
            'readerPassword!',
            $encryption,
            $port,
        );

        $this->assertSame($expected, $connection->endpoint($host));
    }

    /**
     * @return iterable<string, array{string, ?int, string}>
     */
    public static function provideFormatsIPv6Endpoints(): iterable
    {
        yield 'LDAPS default port' => ['ldaps', null, 'ldaps://[2001:db8::1]:636'];

        yield 'StartTLS default port' => ['starttls', null, 'ldap://[2001:db8::1]:389'];

        yield 'StartTLS custom port' => ['starttls', 1389, 'ldap://[2001:db8::1]:1389'];
    }

    #[DataProvider('provideRejectsInvalidBindPassword')]
    public function testRejectsInvalidBindPassword(string $password): void
    {
        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('invalid_bind_secret');

        new LDAPConnection(
            'test',
            ['ldap.example.test'],
            'dc=example,dc=test',
            'cn=reader,dc=example,dc=test',
            $password,
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRejectsInvalidBindPassword(): iterable
    {
        yield 'empty password' => [''];

        yield 'NUL byte' => ["reader\0Password!"];
    }
}
