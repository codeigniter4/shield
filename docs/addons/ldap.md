# LDAP and Active Directory Authentication

The LDAP authenticator verifies web login credentials against an LDAPv3
directory, such as Active Directory or OpenLDAP. It uses Shield's sessions
and login actions. LDAP is opt-in and requires PHP's `ldap` extension.
Follow the [Shield installation](../getting_started/install.md) before configuring LDAP.

## Directory and TLS Prerequisites

1. Create a read-only service account. Give it access to the configured search
   base, login attributes, immutable identifier, and any attributes used by
   eligibility rules or profile mappings.
2. Install a current build of the PHP LDAP extension linked against OpenLDAP in
   the PHP process that serves the application. Confirm it is loaded with
   `php --ri ldap` in the CLI and in the web runtime.
3. Configure the OpenLDAP client trust store before starting PHP. For example,
   set the following in the client configuration file used by PHP (commonly
   `/etc/ldap/ldap.conf`):

    ```text
    TLS_CACERT /etc/ssl/certs/corporate-ldap-ca.pem
    TLS_REQCERT demand
    TLS_REQSAN demand
    ```

    A service manager can also supply `LDAPTLS_CACERT` as a process environment
    variable. Mount a trusted CA bundle in the runtime and restart PHP workers
    after changing their trust configuration. The native transport uses one
    process-wide trust policy. All endpoints must present a trusted chain and a
    certificate valid for the configured hostname. [OpenLDAP TLS reference](https://www.openldap.org/doc/admin26/tls.html)

Use LDAPS for an endpoint that negotiates TLS as soon as it accepts a connection
(commonly port 636). Use StartTLS for an endpoint that upgrades LDAP to TLS before
either bind (commonly port 389). Shield refuses plaintext simple binds and does
not chase LDAP referrals.

## Configure Active Directory

Create **app/Config/AuthLDAP.php** and define the directory settings in
`$connections`. The array key is the connection ID.

```php
<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthLDAP as ShieldAuthLDAP;

class AuthLDAP extends ShieldAuthLDAP
{
    public string $defaultConnection = 'corporate';

    public array $connections = [
        'corporate' => [
            'hosts'               => ['dc01.example.com', 'dc02.example.com'],
            'baseDn'              => 'DC=example,DC=com',
            'bindDn'              => 'CN=shield-reader,OU=Service Accounts,DC=example,DC=com',
            'bindPassword'        => '',
            'encryption'          => 'ldaps',
            'port'                => 636,
            'userBaseDn'          => 'OU=Users,DC=example,DC=com',
            'loginFilter'         => '(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
            'loginAttributes'     => ['sAMAccountName', 'userPrincipalName'],
            'externalIdAttribute' => 'objectGUID',
            'profileAttributes'   => ['username' => 'sAMAccountName'],
            'provisionUsers'      => true,
        ],
    ];
}
```

Enable LDAP and supply the service-account password in your local CodeIgniter
`.env` file. Keep that file out of source control and limit the service account
to reading the necessary entries and attributes.

```ini
authldap.enabled = true
authldap.connections.corporate.bindPassword = 'replace-with-service-account-password'
```

For deployment through the PHP process environment, use
`authldap_connections_corporate_bindPassword` if the environment requires
underscores. CodeIgniter populates the existing `bindPassword` entry when it
initializes `AuthLDAP`, using the same environment override as other settings.

CodeIgniter can override existing connection settings with environment keys
such as `authldap.connections.corporate.hosts.0` or
`authldap.connections.corporate.port`. Declare connection IDs, option keys,
and list entries in the PHP array before overriding their values.

!!! warning

    Shield redacts bind passwords in `var_dump()` output. Direct reads of
    `$connections`, configuration exports, and CodeIgniter's configuration
    cache can expose the password. To keep the password outside the cache,
    retrieve it through an application-owned connection provider. See
    [Administrative diagnostics](#administrative-diagnostics).

List endpoints in preferred order within a connection. Each endpoint in that
list must reach the same logical directory and enforce equivalent access rules.
To route UPNs to different directory configurations, map their lower-case domain
suffixes to connection IDs in `$loginDomains`. For example, `['research.example.org'
=> 'research']` routes `alice@research.example.org` to the `research` connection.
The default connection handles unqualified logins and unmapped suffixes. The
submitted value remains the lookup value, including any suffix.

## Configure OpenLDAP

In the configuration above, set `$defaultConnection` to `'openldap'` and use
the following `$connections` property. The server must support StartTLS on
the configured port.

```php
public array $connections = [
    'openldap' => [
        'hosts'               => ['ldap.example.org'],
        'baseDn'              => 'dc=example,dc=org',
        'bindDn'              => 'cn=shield-reader,ou=services,dc=example,dc=org',
        'bindPassword'        => '',
        'encryption'          => 'starttls',
        'port'                => 389,
        'userBaseDn'          => 'ou=people,dc=example,dc=org',
        'loginFilter'         => '(objectClass=inetOrgPerson)',
        'loginAttributes'     => ['uid'],
        'externalIdAttribute' => 'entryUUID',
        'profileAttributes'   => ['username' => 'uid'],
        'provisionUsers'      => true,
    ],
];
```

Enable LDAP and provide the password for this connection ID in `.env`.

```ini
authldap.enabled = true
authldap.connections.openldap.bindPassword = 'replace-with-service-account-password'
```

The process environment can supply the same value through
`authldap_connections_openldap_bindPassword`.

For another LDAPv3 directory, configure the login attributes, entry filter, and
immutable identifier according to its schema. The configured identifier must
be unique, readable, and stable across user renames and moves.

## Enable Shield Web Login

In **app/Config/Auth.php**, add the `ldap` entry to the existing
`$authenticators` array. Shield's session filter and login actions use the
`session` alias to read the session created by LDAP. Keep
`$defaultAuthenticator` set to `'session'`. Add the LDAP entry alongside
the default authenticators.

```php
public array $authenticators = [
    'tokens'  => \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::class,
    'session' => \CodeIgniter\Shield\Authentication\Authenticators\Session::class,
    'hmac'    => \CodeIgniter\Shield\Authentication\Authenticators\HmacSha256::class,
    'ldap'    => \CodeIgniter\Shield\Authentication\Authenticators\LDAP::class,
];
```

Set the `login` entry in the existing `$views` array to
`'\App\Views\auth\ldap_login'`. For the routes shown below, set
`allowRemembering` to `false` in the existing `$sessionConfig` array. Set
the following properties so the login form displays only links for routes
registered in the example.

```php
public bool $allowRegistration = false;
public bool $allowMagicLinkLogins = false;
```

Copy **vendor/codeigniter4/shield/src/Views/login.php** to
**app/Views/auth/ldap_login.php**. Replace the email field and its label with
a text field named `username` so users can enter a simple login name or a UPN.
Keep the password field and the CSRF field.

```html
<label for="ldapUsername"><?= lang('Auth.username') ?></label>
<input id="ldapUsername" type="text" name="username" autocomplete="username" required>
```

Add a login action to your application's controller. The following example
uses **app/Controllers/Login.php**. After a successful LDAP sign-in, the action
redirects to Shield's login action if one is pending, or to the configured
login destination.

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\LDAP;

final class Login extends BaseController
{
    /**
     * Signs the user in with LDAP and continues any pending Shield login action.
     */
    public function loginAction(): RedirectResponse
    {
        /** @var LDAP $ldap */
        $ldap   = auth('ldap')->getAuthenticator();
        $result = $ldap->attempt($this->request->getPost(['username', 'password']));

        if (! $result->isOK()) {
            return redirect()->route('login')->with('error', $result->reason());
        }

        return $ldap->hasAction()
            ? redirect()->route('auth-action-show')->withCookies()
            : redirect()->to(config('Auth')->loginRedirect())->withCookies();
    }
}
```

In **app/Config/Routes.php**, use Shield's controller for the login form and
register the application controller method for the POST route. This keeps the
route definitions compatible with route caching.

```php
service('auth')->routes($routes, ['except' => ['login', 'register', 'magic-link']]);
$routes->get('login', '\CodeIgniter\Shield\Controllers\LoginController::loginView', ['as' => 'login']);
$routes->post('login', '\App\Controllers\Login::loginAction', ['filter' => 'auth-rates']);
```

Enable the `csrf` filter for the POST route and set `$csrfProtection` to
`'session'` in **app/Config/Security.php**. Apply Shield's `auth-rates`
filter to the authentication action routes as well. [Shield controller filters](../references/controller_filters.md#rate-limiting)

The failure redirect flashes the error message. CodeIgniter's `withInput()`
would copy the submitted password to session flash data. Set
`zend.exception_ignore_args=On` in production PHP settings to keep submitted
credentials out of exception traces in application code.

## Login Eligibility

The directory evaluates an administrator-defined login filter together with
equality tests for the configured login attributes. For Active Directory, use
`sAMAccountName` or `userPrincipalName`. For OpenLDAP, use an attribute such as
`uid`. Case matching follows the directory attribute's equality rule. Shield
requires one eligible match and escapes the submitted value as an LDAP filter
assertion. Keep raw filter syntax restricted to trusted configuration.

This Active Directory filter excludes disabled accounts and requires transitive
membership in `Shield Users`. It can be configured as the `eligibilityFilter`
and is checked on the matched user after a successful user bind:

```ldap
(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(memberOf:1.2.840.113556.1.4.1941:=CN=Shield Users,OU=Groups,DC=example,DC=com))
```

Confirm that `Shield Users` is the intended security group, and test nested and
primary group membership behavior in your directory. Active Directory's
`memberOf` matching follows its directory links. For OpenLDAP with a `memberOf`
overlay, a direct eligibility filter can use
`(&(objectClass=inetOrgPerson)(memberOf=cn=shield-users,ou=groups,dc=example,dc=org))`.
Any OpenLDAP nested membership rule depends on the installed directory schema
and overlays. [Active Directory matching rules](https://learn.microsoft.com/en-us/windows/win32/adsi/search-filter-syntax)

Grouping operators `&`, `|`, and `!` can be nested. LDAP filter values use
[RFC 4515](https://www.rfc-editor.org/rfc/rfc4515) escaping. If a filter compares
a distinguished name, first encode the distinguished name according to
[RFC 4514](https://www.rfc-editor.org/rfc/rfc4514), then encode that string as a
filter assertion. An escaped comma in a DN, for example `CN=Family\, Given`, is
written as `CN=Family\5c, Given` in a filter assertion.

## Provisioning and Migration from Local Accounts

The link between a directory account and a Shield user is scoped to a connection
ID and an immutable directory identifier. Use `objectGUID` for Active Directory
or `entryUUID` for OpenLDAP. Keep connection IDs stable. Replicas for a single
directory belong to the same connection. Give independent directories separate
connection IDs, even when their users have similar login attributes.

For a new application, enable just-in-time provisioning to create a Shield user
after the directory verifies the password. Map the local `username` field to a
single-valued directory attribute that produces valid, unique Shield usernames
of at most 30 characters. To map additional attributes, create columns and
permit them in your [custom user provider](../customization/user_provider.md).
For example, map `directory_email` to `mail` and `first_name` to `givenName`.
Unconfigured fields stay under application control. Profile attributes used by
application security decisions should be writable by trusted directory
administrators only. Set `'updateProfile' => false` on the connection to use the
configured attributes at provisioning only.

For a migration, start with provisioning disabled. From a trusted operator
workflow, verify the account in the directory and confirm which Shield user
should own it. Then explicitly link the two identities:

```php
use CodeIgniter\Shield\Authentication\LDAP\LDAPUserResolver;

$resolver = new LDAPUserResolver(auth()->getProvider());
$resolver->link('corporate', '00112233-4455-6677-8899-aabbccddeeff', 42);
```

The example uses a canonical AD GUID and Shield user ID `42`. Convert the
16-byte LDAP `objectGUID` value with `LDAPUserResolver::normalizeObjectGuid()`
when reading it directly from LDAP. Use the `entryUUID` string as returned by
OpenLDAP. A link conflicts if the same directory identity is already linked
to another Shield user. Renaming an account or moving it to a new DN preserves
the link as long as the updated account still meets the directory search and
eligibility configuration. A conflicting username during attribute updates
blocks the login until the conflict is resolved. The resolver owns a database
transaction for each write operation. Call it outside other transactions on
the Shield database connection.

Shield never saves the submitted LDAP password or overwrites an existing local
password. Directory email belongs in an application profile attribute if you
need it. Use an independently verified contact method for Shield's email-based
login action, or choose an action that supports directory-only users without an
email identity. Review [Shield authentication actions](../references/authentication/auth_actions.md)
before enabling just-in-time provisioning. If registration activation is
configured, a newly provisioned account remains blocked until the application
activates it through its trusted activation workflow.

Users without a Shield password reset their password through the directory's
account recovery process. Before enabling the `force-reset` filter on routes
used by LDAP accounts, include Shield's fix for users without an email/password
identity. [Force-reset fix](https://github.com/codeigniter4/shield/pull/1411)

## Local-Only, LDAP-Only, and Hybrid Login

For local-only login, keep the ordinary `Session` authenticator under the
`session` alias. For LDAP-only login, configure `LDAP` as described above and
leave `$localAccounts` empty. An application can enable hybrid login by listing
the identifiers and corresponding Shield user IDs assigned to local
password login in **app/Config/AuthLDAP.php**:

```php
public array $localAccounts = ['local-operator' => 10];
```

Reserve these identifiers for local users. Matching is case-insensitive, and
configuration rejects duplicate identifiers that differ only in case. Shield
verifies the local password for the specified user ID and requires a pending
application login action before the operator can sign in. Configure an MFA
action that applies to each operator and can be completed during a directory
outage. Every other identifier goes to its selected LDAP connection. Directory
credential failures and outages both deny a new LDAP login.

## Availability and Session Revocation

LDAP password login verifies the submitted credentials with the directory.
The authenticator disables remember-me token issuance on password login. Keep
`allowRemembering` disabled if previous installations issued remember-me cookies
and users must prove their directory credentials on the next sign-in. Revoke
pre-migration remember-me tokens before re-enabling the feature.

Directory eligibility is checked during password login. Existing Shield
sessions and issued API tokens follow their existing lifetime and revocation
policies. When disabling a directory account, also revoke affected application
sessions and tokens using the application's established procedures. If LDAP is
the sole permitted web sign-in method, disable public registration and magic
link routes in Shield, and review any custom password reset, login, or token
issuance routes that could provide another sign-in path.

## Administrative Diagnostics

Application services can use the directory transport to test configured
connections and preview a bounded search without provisioning users or changing
authorization. Restrict administrative endpoints with application authorization,
CSRF protection, and rate limits. Request only the attributes an operator needs,
and keep raw results out of ordinary application logs.

```php
use CodeIgniter\Shield\Authentication\LDAP\LDAPFilter;
use CodeIgniter\Shield\Exceptions\LDAPException;

$config        = config('AuthLDAP');
$connectionIds = $config->connectionIds();
$directory     = service('ldapDirectory');

try {
    $connection = $config->getConnection('corporate');
    $directory->testConnection($connection);
    $directory->testBind($connection);

    $entries = $directory->preview(
        $connection,
        $connection->userBaseDn ?? $connection->baseDn,
        LDAPFilter::all($connection->loginFilter, $connection->eligibilityFilter),
        ['sAMAccountName', 'displayName'],
        20,
    );
} catch (LDAPException $exception) {
    log_message('warning', 'LDAP diagnostic failed ({reason}).', ['reason' => $exception->reason]);
}
```

To inspect groups, pass the group base DN and a trusted group filter to
`preview()`. The search limit is 1 to 100 entries. LDAP attribute names in
returned entries are lowercase and values may be binary.

To check a user's credentials without provisioning a Shield user, combine the
connection's login filter and `LDAPFilter::equal($attribute, $identifier)`
with `LDAPFilter::all()`. Pass the resulting filter, the submitted password,
and the connection's eligibility filter to the directory's `authenticate()`
method. Discard the password after the operation. Use `auth('ldap')->attempt()`
for application sign-in and let Shield complete any configured login actions.

To fetch connection settings from a database or another application source,
override `AuthLDAP::getConnection()` and `AuthLDAP::connectionIds()`. In
`getConnection()`, read the credential from your secret store and pass its
string value as `bindPassword` to the `LDAPConnection` constructor. Present
only connection IDs and the operational metadata your administrators need.

### Troubleshooting

- For connection or TLS errors, confirm DNS, port, certificate chain, hostname,
  and the CA bundle loaded by the web runtime. The following command prompts
  for the service password.

    ```console
    ldapsearch -H ldaps://dc01.example.com:636 -x -D '{bind DN}' -W \
      -b 'DC=example,DC=com' -s base '(objectClass=*)' dn
    ```

    For StartTLS, use an `ldap://` URI and `-ZZ` to require the upgrade.

- For a service bind failure, verify the service DN, secret, and directory policy.
  For a missing or incomplete result, verify the search base, access controls,
  filters, and timeout settings. Keep searches within the correct naming context
  since referrals are rejected.
- If a credential check succeeds but Shield login still fails, check for a
  missing link, a disabled provisioning policy, a conflicting username, or a
  local account ban or activation requirement. LDAP-specific logs use reason
  codes, and the browser receives Shield's generic login error message.

## Authorization

Directory group filters control eligibility during LDAP sign-in. Manage
application group assignments, permissions, and revocation through the
[Shield authorization APIs](../references/authorization.md).

## Testing Against OpenLDAP

The **OpenLDAP Integration Tests** workflow runs
`NativeLDAPDirectoryIntegrationTest` against the Docker fixture in
`tests/_support/LDAP`. It uses test certificates and waits for the fixture to
accept service-account binds over both LDAPS and StartTLS. The integration
test run fails if any test is skipped.

To run the same checks locally, install the test dependencies and make sure
Docker, OpenSSL, and PHP's `ldap` extension are available. Run the following
command from the Shield checkout.

```bash
bash tests/_support/LDAP/run-integration-tests.sh
```

The script publishes the fixture's ports on loopback and stops the container
when the run finishes. The regular PHPUnit suite can run without the fixture.
Test AD-specific GUIDs, account filters, and transitive group membership
separately against a disposable AD DS or Samba AD test domain.
