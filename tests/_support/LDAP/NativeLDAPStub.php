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

namespace Tests\Support\LDAP;

/**
 * Scripted state for the LDAP functions loaded in isolated PHPUnit processes.
 */
final class NativeLDAPStub
{
    public static bool $available = true;

    /**
     * @var list<string>
     */
    public static array $connected = [];

    public static int $unbound = 0;

    /**
     * @var array<string, int> Endpoints whose service bind fails with the given LDAP code.
     */
    public static array $bindErrors = [];

    public static bool $warnOnBindFailure = false;
    public static int $lastError          = 0;
    public static string $diagnostic      = '';
    public static bool $searchFails       = false;
    public static int $searchCode         = 0;
    public static bool $hasReference      = false;

    /**
     * @var list<string> Distinguished names in the search result.
     */
    public static array $entries = [];
}
