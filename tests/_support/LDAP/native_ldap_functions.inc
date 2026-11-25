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

use LDAP\Connection;
use ReflectionClass;
use stdClass;
use Tests\Support\LDAP\NativeLDAPStub;

// Keep these shadow functions in an include used only by the isolated transport tests.
if (! \extension_loaded('ldap')) {
    class_alias(NativeLDAPStub::class, Connection::class);

    foreach ([
        'LDAP_OPT_NETWORK_TIMEOUT'    => 20_485,
        'LDAP_OPT_TIMEOUT'            => 20_482,
        'LDAP_OPT_X_TLS_REQUIRE_CERT' => 24_582,
        'LDAP_OPT_X_TLS_DEMAND'       => 2,
        'LDAP_OPT_X_TLS_HARD'         => 1,
        'LDAP_OPT_PROTOCOL_VERSION'   => 17,
        'LDAP_OPT_REFERRALS'          => 8,
        'LDAP_OPT_DEREF'              => 2,
        'LDAP_DEREF_NEVER'            => 0,
        'LDAP_OPT_DIAGNOSTIC_MESSAGE' => 50,
    ] as $option => $value) {
        define($option, $value);
    }
}

function extension_loaded(string $extension): bool
{
    return $extension === 'ldap' ? NativeLDAPStub::$available : \extension_loaded($extension);
}

function function_exists(string $function): bool
{
    return $function === 'ldap_start_tls' || \function_exists($function);
}

function ldap_connect(string $uri): Connection|false
{
    NativeLDAPStub::$connected[] = $uri;

    // The native constructor creates a handle without connecting to the URI.
    return \extension_loaded('ldap')
        ? \ldap_connect($uri)
        : (new ReflectionClass(Connection::class))->newInstance();
}

function ldap_get_option(Connection $connection, int $option, int|string|null &$value): bool
{
    $value = match ($option) {
        LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_DEMAND,
        LDAP_OPT_DIAGNOSTIC_MESSAGE => NativeLDAPStub::$diagnostic,
        default                     => null,
    };

    return $value !== null;
}

function ldap_set_option(Connection $connection, int $option, bool|int $value): bool
{
    return true;
}

function ldap_bind(Connection $connection, string $dn, string $password): bool
{
    $uri                       = NativeLDAPStub::$connected[array_key_last(NativeLDAPStub::$connected)];
    NativeLDAPStub::$lastError = NativeLDAPStub::$bindErrors[$uri] ?? 0;

    if (NativeLDAPStub::$lastError !== 0 && NativeLDAPStub::$warnOnBindFailure) {
        trigger_error('Untrusted LDAP bind diagnostic', E_USER_WARNING);
    }

    return NativeLDAPStub::$lastError === 0;
}

function ldap_unbind(Connection $connection): bool
{
    NativeLDAPStub::$unbound++;

    return true;
}

function ldap_errno(Connection $connection): int
{
    return NativeLDAPStub::$lastError;
}

/**
 * @param list<string> $attributes
 */
function ldap_search(Connection $connection, string $base, string $filter, array $attributes, int $attributesOnly, int $sizeLimit, int $timeLimit): false|stdClass
{
    NativeLDAPStub::$lastError = NativeLDAPStub::$searchCode;

    return NativeLDAPStub::$searchFails ? false : new stdClass();
}

function ldap_parse_result(Connection $connection, stdClass $result, ?int &$code): bool
{
    $code = NativeLDAPStub::$searchCode;

    return true;
}

function ldap_first_reference(Connection $connection, stdClass $result): false|stdClass
{
    return NativeLDAPStub::$hasReference ? new stdClass() : false;
}

function ldap_count_entries(Connection $connection, stdClass $result): int
{
    return count(NativeLDAPStub::$entries);
}

function ldap_first_entry(Connection $connection, stdClass $result): false|int
{
    return NativeLDAPStub::$entries === [] ? false : 0;
}

function ldap_next_entry(Connection $connection, int $entry): false|int
{
    $next = $entry + 1;

    return array_key_exists($next, NativeLDAPStub::$entries) ? $next : false;
}

function ldap_get_dn(Connection $connection, int $entry): string
{
    return NativeLDAPStub::$entries[$entry];
}

function ldap_get_values_len(Connection $connection, int $entry, string $attribute): false
{
    // These cases only inspect distinguished names, so no attributes are returned.
    return false;
}
