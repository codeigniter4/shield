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

namespace CodeIgniter\Shield\Tests\Authentication\LDAP\Integration;

use CodeIgniter\Shield\Authentication\LDAP\LDAPConnection;
use CodeIgniter\Shield\Authentication\LDAP\LDAPFilter;
use CodeIgniter\Shield\Authentication\LDAP\NativeLDAPDirectory;
use CodeIgniter\Shield\Exceptions\LDAPException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class NativeLDAPDirectoryIntegrationTest extends TestCase
{
    private const BASE_DN       = 'dc=example,dc=test';
    private const USER_BASE_DN  = 'ou=people,' . self::BASE_DN;
    private const GROUP_BASE_DN = 'ou=groups,' . self::BASE_DN;

    #[DataProvider('provideUsesVerifiedTlsForLookupAndAuthentication')]
    public function testUsesVerifiedTlsForLookupAndAuthentication(string $encryption): void
    {
        $connection = $this->connection($encryption);
        $directory  = new NativeLDAPDirectory();

        $directory->testConnection($connection);
        $directory->testBind($connection);

        $entry = $directory->authenticate($connection, self::USER_BASE_DN, '(uid=alice)', ['uid', 'sn'], 'alicePassword!');

        $this->assertSame('uid=alice,' . self::USER_BASE_DN, $entry['dn']);
        $this->assertSame(['alice'], $entry['attributes']['uid']);
        $this->assertSame(['Shared'], $entry['attributes']['sn']);
        $this->assertMatchesRegularExpression('/\A[a-f0-9-]{36}\z/', $entry['attributes']['entryuuid'][0]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideUsesVerifiedTlsForLookupAndAuthentication(): iterable
    {
        yield 'StartTLS' => ['starttls'];

        yield 'LDAPS' => ['ldaps'];
    }

    public function testRejectsWrongCertificateHostname(): void
    {
        $host = getenv('SHIELD_LDAP_TEST_WRONG_HOST');

        if ($host === false || $host === '') {
            $this->markTestSkipped('Set SHIELD_LDAP_TEST_WRONG_HOST to test certificate hostname verification.');
        }

        $connection = $this->connection('starttls', [$host]);

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('certificate_verification_failed');

        (new NativeLDAPDirectory())->testConnection($connection);
    }

    #[RunInSeparateProcess]
    public function testRejectsUntrustedCertificateChain(): void
    {
        $ca = getenv('SHIELD_LDAP_TEST_UNTRUSTED_CA');

        if ($ca === false || $ca === '') {
            $this->markTestSkipped('Set SHIELD_LDAP_TEST_UNTRUSTED_CA to another CA bundle to test certificate trust.');
        }

        $connection = $this->connection();
        putenv('LDAPTLS_CACERT=' . $ca);
        putenv('LDAPTLS_CACERTDIR=');

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('certificate_verification_failed');

        (new NativeLDAPDirectory())->testConnection($connection);
    }

    public function testRejectsAmbiguousSearchBeforeCheckingPassword(): void
    {
        $connection = $this->connection();

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('ambiguous_user');

        (new NativeLDAPDirectory())->authenticate($connection, self::USER_BASE_DN, '(sn=Shared)', [], 'alicePassword!');
    }

    public function testAppliesEligibilityFilterAfterBind(): void
    {
        $connection = $this->connection();

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('ineligible_user');

        (new NativeLDAPDirectory())->authenticate($connection, self::USER_BASE_DN, '(uid=alice)', [], 'alicePassword!', '(uid=bob)');
    }

    public function testRejectsInvalidCredentials(): void
    {
        $connection = $this->connection();

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('invalid_credentials');

        (new NativeLDAPDirectory())->authenticate($connection, self::USER_BASE_DN, '(uid=alice)', [], 'wrong-password');
    }

    public function testEscapesSubmittedIdentifier(): void
    {
        $connection = $this->connection();

        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('user_not_found');

        (new NativeLDAPDirectory())->authenticate(
            $connection,
            self::USER_BASE_DN,
            LDAPFilter::equal('uid', 'alice*)(uid=*'),
            [],
            'alicePassword!',
        );
    }

    public function testPreviewReturnsABoundedSample(): void
    {
        $entries = (new NativeLDAPDirectory())->preview($this->connection(), self::USER_BASE_DN, '(sn=Shared)', ['uid'], 1);

        $this->assertCount(1, $entries);
        $this->assertContains($entries[0]['attributes']['uid'][0], ['alice', 'bob']);
    }

    public function testPreviewFindsGroupMembershipByEntryDn(): void
    {
        $connection = $this->connection();
        $directory  = new NativeLDAPDirectory();
        $aliceDn    = 'uid=alice,' . self::USER_BASE_DN;
        $bobDn      = 'uid=bob,' . self::USER_BASE_DN;
        $group      = '(objectClass=groupOfNames)';

        $entries = $directory->preview(
            $connection,
            self::GROUP_BASE_DN,
            LDAPFilter::all($group, LDAPFilter::equal('member', $aliceDn)),
            ['cn', 'member'],
        );

        $this->assertSame([[
            'dn'         => 'cn=shield-users,' . self::GROUP_BASE_DN,
            'attributes' => ['cn' => ['shield-users'], 'member' => [$aliceDn]],
        ]], $entries);

        $this->assertSame([], $directory->preview(
            $connection,
            self::GROUP_BASE_DN,
            LDAPFilter::all($group, LDAPFilter::equal('member', $bobDn)),
            ['cn', 'member'],
        ));
    }

    public function testAuthenticationDoesNotReturnAttributesProtectedFromTheServiceAccount(): void
    {
        $entry = (new NativeLDAPDirectory())->authenticate(
            $this->connection(),
            self::USER_BASE_DN,
            '(uid=alice)',
            ['uid', 'userPassword'],
            'alicePassword!',
        );

        $this->assertSame(['alice'], $entry['attributes']['uid']);
        $this->assertArrayNotHasKey('userpassword', $entry['attributes']);
    }

    public function testUsesNextEndpointWhenTheFirstIsUnavailable(): void
    {
        $connection = $this->connection('starttls', ['127.0.0.3', '127.0.0.1']);
        $entry      = (new NativeLDAPDirectory())->authenticate($connection, self::USER_BASE_DN, '(uid=alice)', ['uid'], 'alicePassword!');

        $this->assertSame(['alice'], $entry['attributes']['uid']);
    }

    /**
     * @param list<string> $hosts
     */
    private function connection(string $encryption = 'starttls', array $hosts = ['127.0.0.1']): LDAPConnection
    {
        $port = getenv($encryption === 'ldaps' ? 'SHIELD_LDAP_TEST_LDAPS_PORT' : 'SHIELD_LDAP_TEST_PORT');

        if (! extension_loaded('ldap') || $port === false || ! ctype_digit($port)) {
            $this->markTestSkipped('Start the OpenLDAP fixture and set its environment variables to run this test.');
        }

        return new LDAPConnection(
            'test',
            $hosts,
            self::BASE_DN,
            'cn=reader,' . self::BASE_DN,
            'readerPassword!',
            $encryption,
            (int) $port,
        );
    }
}
