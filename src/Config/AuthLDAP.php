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

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Shield\Authentication\LDAP\LDAPConnection;
use CodeIgniter\Shield\Exceptions\LDAPException;
use Throwable;

/**
 * Opt-in LDAP settings for choosing a directory or an explicitly reserved local account at login.
 */
class AuthLDAP extends BaseConfig
{
    /**
     * Gate LDAP login even when the authenticator alias is registered.
     */
    public bool $enabled = false;

    /**
     * Connection ID used when the login identifier has no mapped UPN suffix.
     */
    public string $defaultConnection = '';

    /**
     * Directory settings keyed by connection ID. Declare bindPassword to allow
     * CodeIgniter to override it through authldap.connections.<id>.bindPassword.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $connections = [
        // 'corporate' => [
        //     'hosts'               => ['dc01.example.com', 'dc02.example.com'],
        //     'baseDn'              => 'DC=example,DC=com',
        //     'bindDn'              => 'CN=shield-reader,OU=Service Accounts,DC=example,DC=com',
        //     'bindPassword'        => '',
        //     'encryption'          => 'ldaps',
        //     'userBaseDn'          => 'OU=Users,DC=example,DC=com',
        //     'loginFilter'         => '(&(objectCategory=person)(objectClass=user))',
        //     'loginAttributes'     => ['sAMAccountName', 'userPrincipalName'],
        //     'externalIdAttribute' => 'objectGUID',
        //     'profileAttributes'   => ['username' => 'sAMAccountName'],
        // ],
    ];

    /**
     * @var array<string, string> Lowercase UPN suffix to connection ID.
     */
    public array $loginDomains = [];

    /**
     * @var array<string, int|string> Case-insensitive login identifier to expected local user ID.
     */
    public array $localAccounts = [];

    /**
     * Retrieve the settings for the specified connection ID.
     *
     * Override this method to load a named connection from an application-owned provider.
     *
     * @throws LDAPException If the connection settings or bind password are missing or invalid.
     */
    public function getConnection(string $id): LDAPConnection
    {
        $settings = $this->connections[$id] ?? null;

        if (! is_array($settings)
            || ! isset($settings['hosts'], $settings['baseDn'], $settings['bindDn'])
            || array_filter($settings, is_string(...), ARRAY_FILTER_USE_KEY) !== $settings
        ) {
            throw new LDAPException('invalid_configuration');
        }

        try {
            return new LDAPConnection(
                $id,
                $settings['hosts'],
                $settings['baseDn'],
                $settings['bindDn'],
                $settings['bindPassword'] ?? '',
                ...array_diff_key($settings, ['hosts' => true, 'baseDn' => true, 'bindDn' => true, 'bindPassword' => true]),
            );
        } catch (Throwable $exception) {
            // The constructor received the raw bind password as an argument.
            throw new LDAPException($exception instanceof LDAPException ? $exception->reason : 'invalid_configuration');
        }
    }

    /**
     * List the connection IDs declared in the configuration.
     *
     * @return list<string>
     */
    public function connectionIds(): array
    {
        return array_keys($this->connections);
    }

    /**
     * Omit bind passwords from the PHP debug representation of this config.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        $configuration = get_object_vars($this);

        foreach (array_filter($this->connections, is_array(...)) as $id => $settings) {
            $configuration['connections'][$id] = array_diff_key($settings, ['bindPassword' => true]);
        }

        return $configuration;
    }
}
