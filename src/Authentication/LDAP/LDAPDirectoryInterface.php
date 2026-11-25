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

namespace CodeIgniter\Shield\Authentication\LDAP;

use CodeIgniter\Shield\Exceptions\LDAPException;

/**
 * Directory operations used for LDAP sign-in and application diagnostics.
 */
interface LDAPDirectoryInterface
{
    /**
     * Check that the configured endpoint can establish and verify TLS.
     *
     * @throws LDAPException If no configured endpoint completes the TLS check.
     */
    public function testConnection(LDAPConnection $connection): void;

    /**
     * Check that an endpoint accepts the configured service account.
     *
     * @throws LDAPException If the service bind fails.
     */
    public function testBind(LDAPConnection $connection): void;

    /**
     * Search for one user, verify their password, and apply the eligibility filter.
     *
     * @param list<string> $attributes
     *
     * @return array{dn: string, attributes: array<string, list<string>>} Attribute names are lowercase.
     *
     * @throws LDAPException If the directory cannot verify one eligible user.
     */
    public function authenticate(
        LDAPConnection $connection,
        string $baseDn,
        string $filter,
        array $attributes,
        string $password,
        string $eligibilityFilter = '(objectClass=*)',
    ): array;

    /**
     * Return a bounded sample using the service account.
     *
     * @param list<string> $attributes
     *
     * @return list<array{dn: string, attributes: array<string, list<string>>}> Attribute names are lowercase.
     *
     * @throws LDAPException If the search fails or its inputs are invalid.
     */
    public function preview(
        LDAPConnection $connection,
        string $baseDn,
        string $filter,
        array $attributes,
        int $limit = 20,
    ): array;
}
