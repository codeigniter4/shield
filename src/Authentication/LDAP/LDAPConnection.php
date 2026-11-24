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

use Closure;
use CodeIgniter\Shield\Exceptions\LDAPException;

/**
 * Immutable settings and a bind credential for an LDAP directory.
 */
final readonly class LDAPConnection
{
    /**
     * @var Closure(): string
     */
    private Closure $readBindPassword;

    /**
     * Validate the directory settings and bind credential.
     *
     * @param array<int, string>    $hosts
     * @param array<int, string>    $loginAttributes
     * @param array<string, string> $profileAttributes Local user field to LDAP attribute
     *
     * @throws LDAPException If the connection settings are invalid.
     */
    public function __construct(
        public string $id,
        public array $hosts,
        public string $baseDn,
        public string $bindDn,
        string $bindPassword,
        public string $encryption = 'ldaps',
        public ?int $port = null,
        public int $connectTimeout = 5,
        public int $searchTimeout = 5,
        public ?string $userBaseDn = null,
        public string $loginFilter = '(objectClass=person)',
        public array $loginAttributes = ['uid'],
        public string $externalIdAttribute = 'entryUUID',
        public array $profileAttributes = [],
        public bool $provisionUsers = false,
        public string $eligibilityFilter = '(objectClass=*)',
        public bool $updateProfile = true,
    ) {
        if (preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}\z/', $id) !== 1
            || $hosts === [] || count($hosts) > 10 || ! array_is_list($hosts)
            || ! in_array($encryption, ['ldaps', 'starttls'], true)
            || ($port !== null && ($port < 1 || $port > 65535))
            || $connectTimeout < 1 || $connectTimeout > 60
            || $searchTimeout < 1 || $searchTimeout > 60
            || $loginAttributes === [] || count($loginAttributes) > 10 || ! array_is_list($loginAttributes)
            || array_filter($hosts, is_string(...)) !== $hosts
            || array_filter($loginAttributes, is_string(...)) !== $loginAttributes
            || array_filter($profileAttributes, is_string(...)) !== $profileAttributes
        ) {
            throw new LDAPException('invalid_configuration');
        }

        foreach ($hosts as $host) {
            if (filter_var($host, FILTER_VALIDATE_IP) === false
                && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false
            ) {
                throw new LDAPException('invalid_endpoint');
            }
        }

        foreach ([$baseDn, $userBaseDn ?? $baseDn, $bindDn] as $dn) {
            if (trim($dn) === '' || str_contains($dn, "\0")) {
                throw new LDAPException('invalid_configuration');
            }
        }

        if ($bindPassword === '' || str_contains($bindPassword, "\0")) {
            throw new LDAPException('invalid_bind_secret');
        }

        LDAPFilter::validate($loginFilter);
        LDAPFilter::validate($eligibilityFilter);

        foreach ([$externalIdAttribute, ...$loginAttributes, ...array_values($profileAttributes)] as $attribute) {
            LDAPFilter::validateAttribute($attribute);
        }

        // A closure keeps the secret out of JSON, var_export(), and serialization.
        $this->readBindPassword = static fn (): string => $bindPassword;
    }

    /**
     * Return the service account credential for the bind operation.
     */
    public function bindPassword(): string
    {
        return ($this->readBindPassword)();
    }

    /**
     * Build a URI for a configured host with the configured or protocol-default port.
     */
    public function endpoint(string $host): string
    {
        $host = str_contains($host, ':') ? '[' . $host . ']' : $host;

        return $this->encryption === 'ldaps'
            ? 'ldaps://' . $host . ':' . ($this->port ?? 636)
            : 'ldap://' . $host . ':' . ($this->port ?? 389);
    }

    /**
     * Expose diagnostic connection settings while redacting the bind credential provider.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        $configuration = get_object_vars($this);
        unset($configuration['readBindPassword']);

        return $configuration;
    }
}
