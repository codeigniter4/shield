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

namespace Tests\Authorization;

use CodeIgniter\Shield\Authorization\PermissionMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class PermissionMatcherTest extends TestCase
{
    /**
     * @param list<string> $grants
     */
    #[DataProvider('provideMatches')]
    public function testMatches(string $permission, array $grants, bool $expected): void
    {
        $this->assertSame($expected, PermissionMatcher::matches($permission, $grants));
    }

    /**
     * @return iterable<string, array{string, list<string>, bool}>
     */
    public static function provideMatches(): iterable
    {
        return [
            'exact permission'                                             => ['admin.users.create', ['admin.users.create'], true],
            'uppercase permission does not match lowercase grant'          => ['Admin.Users.Create', ['admin.users.create'], false],
            'uppercase grant does not match lowercase permission'          => ['admin.users.create', ['Admin.Users.Create'], false],
            'different permission'                                         => ['admin.users.create', ['admin.users.delete'], false],
            'trailing wildcard matches child'                              => ['admin.users.create', ['admin.users.*'], true],
            'trailing wildcard matches deeper child'                       => ['admin.users.roles.create', ['admin.users.*'], true],
            'broad trailing wildcard matches child'                        => ['admin.users.create', ['admin.*'], true],
            'trailing wildcard does not match parent'                      => ['admin.users', ['admin.users.*'], false],
            'broad wildcard does not match root permission'                => ['admin', ['admin.*'], false],
            'standalone wildcard does not match child'                     => ['admin.users.create', ['*'], false],
            'leading wildcard does not match child'                        => ['admin.users.create', ['*.users.create'], false],
            'leading trailing wildcard does not match globally'            => ['admin.users.create', ['*.*'], false],
            'exact child permission does not match parent'                 => ['admin.users', ['admin.users.create'], false],
            'middle wildcard matches one segment'                          => ['admin.users.create', ['admin.*.create'], true],
            'middle wildcard does not match multiple segments'             => ['admin.users.roles.create', ['admin.*.create'], false],
            'middle wildcard does not match no segment'                    => ['admin.create', ['admin.*.create'], false],
            'middle wildcard does not match sibling'                       => ['admin.users.delete', ['admin.*.create'], false],
            'middle and trailing wildcards do not match parent'            => ['admin.users.create', ['admin.*.create.*'], false],
            'middle and trailing wildcards match child'                    => ['admin.users.create.view', ['admin.*.create.*'], true],
            'middle and trailing wildcards do not match multiple segments' => ['admin.users.roles.create', ['admin.*.create.*'], false],
            'middle and trailing wildcards require segment'                => ['admin.create', ['admin.*.create.*'], false],
            'multiple wildcards match'                                     => ['admin.users.roles.create', ['admin.*.*.create'], true],
            'multiple wildcards require one segment each'                  => ['admin.users.create', ['admin.*.*.create'], false],
            'wildcard check can match exact wildcard grant'                => ['admin.users.*', ['admin.users.*'], true],
            'empty permission segment does not match'                      => ['admin..create', ['admin.*.create'], false],
            'empty grant segment does not match'                           => ['admin.users.create', ['admin..*'], false],
            'empty segments do not match exactly'                          => ['admin..create', ['admin..create'], false],
            'partial wildcard grant segment does not match'                => ['admin.users.create', ['admin.user*.create'], false],
            'partial wildcard permission segment does not match exactly'   => ['admin.user*.create', ['admin.user*.create'], false],
            'standalone wildcard does not match exactly'                   => ['*', ['*'], false],
            'leading wildcard does not match exactly'                      => ['*.create', ['*.create'], false],
        ];
    }
}
