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

use CodeIgniter\Shield\Authentication\LDAP\NativeLDAPDirectory;
use CodeIgniter\Shield\Config\Services;
use CodeIgniter\Shield\Tests\TestCase;

/**
 * @internal
 */
final class LDAPServicesTest extends TestCase
{
    public function testSharesTheDirectoryByDefault(): void
    {
        $directory = Services::ldapDirectory();

        $this->assertInstanceOf(NativeLDAPDirectory::class, $directory);
        $this->assertSame($directory, Services::ldapDirectory(true));
        $this->assertSame($directory, service('ldapDirectory'));
    }

    public function testNonSharedDirectoryCallsCreateIndependentInstances(): void
    {
        $first  = Services::ldapDirectory(false);
        $second = Services::ldapDirectory(false);
        $shared = Services::ldapDirectory();

        $this->assertInstanceOf(NativeLDAPDirectory::class, $first);
        $this->assertNotSame($first, $second);
        $this->assertNotSame($first, $shared);
        $this->assertNotSame($second, $shared);
    }
}
