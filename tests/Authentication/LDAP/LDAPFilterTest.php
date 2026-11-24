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

use CodeIgniter\Shield\Authentication\LDAP\LDAPFilter;
use CodeIgniter\Shield\Exceptions\LDAPException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class LDAPFilterTest extends TestCase
{
    public function testEscapesFilterOperatorsAndNonAsciiOctets(): void
    {
        $escaped = LDAPFilter::equal('uid', "a*)(objectClass=*)\\\x00\xff");

        $this->assertSame('(uid=a\2a\29\28objectClass=\2a\29\5c\00\ff)', $escaped);
        LDAPFilter::validate($escaped);
    }

    public function testEscapesDnComponents(): void
    {
        $this->assertSame('\23example\2c\3d\20', LDAPFilter::escapeDnComponent('#example,= '));
        $this->assertSame('\20x\00', LDAPFilter::escapeDnComponent(" x\0"));
    }

    public function testCombinesNestedFiltersAndMatchingRules(): void
    {
        $disabled = '(userAccountControl:1.2.840.113556.1.4.803:=2)';
        $login    = LDAPFilter::any(LDAPFilter::equal('sAMAccountName', 'alice'), LDAPFilter::equal('userPrincipalName', 'alice'));
        $filter   = LDAPFilter::all('(objectCategory=person)', $login, LDAPFilter::not($disabled));

        $this->assertSame(
            '(&(objectCategory=person)(|(sAMAccountName=alice)(userPrincipalName=alice))(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
            $filter,
        );
    }

    #[DataProvider('provideRejectsInvalidFilters')]
    public function testRejectsInvalidFilters(string $filter): void
    {
        $this->expectException(LDAPException::class);
        $this->expectExceptionMessage('invalid_filter');

        LDAPFilter::validate($filter);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideRejectsInvalidFilters(): iterable
    {
        yield 'missing parentheses' => ['uid=alice'];

        yield 'multiple root filters' => ['(uid=alice)(uid=bob)'];

        yield 'empty OR' => ['(|)'];

        yield 'multiple NOT children' => ['(!(uid=alice)(uid=bob))'];

        yield 'unescaped open parenthesis' => ['(uid=alice(bob)'];

        yield 'malformed hex escape' => ['(uid=\0x)'];

        yield 'unescaped invalid UTF-8' => ["(uid=\xff)"];

        yield 'missing matching rule' => ['(:=abc)'];

        yield 'raw wildcard in extensible match' => ['(uid:caseExactMatch:=*)'];

        yield 'too deeply nested' => [str_repeat('(!', 40) . '(uid=*)' . str_repeat(')', 40)];
    }
}
