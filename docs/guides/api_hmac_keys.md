# Protecting an API with HMAC Keys

!!! note

    For the purpose of this documentation and to maintain a level of consistency with the Authorization Tokens,
    the term "Token" will be used to represent a set of API Keys (key and secretKey).

HMAC Keys can be used to authenticate users for your own site, or when allowing third-party developers to access your
API. When making requests using HMAC keys, the token should be included in the `Authorization` header as an
`HMAC-SHA256` token.

!!! note

    By default, `$authenticatorHeader['hmac']` is set to `Authorization`. You can change this value by
    setting the `$authenticatorHeader['hmac']` value in the **app/Config/AuthToken.php** config file.

Tokens are issued with the `generateHmacToken()` method on the user. This returns a
`CodeIgniter\Shield\Entities\AccessToken` instance. The `AccessToken` object returned will include a `secret` field
which will be the '**key**' and a `rawSecretKey` field that will be the '**secretKey**'. You should display the
'**secretKey**' to your user immediately, so they have a chance to copy it somewhere safe, as this is the only time
you can reveal this key. The '**key**' and '**secretKey**' are saved to the database. The '**secretKey**' is stored
encrypted.

The `generateHmacToken()` method requires a name for the token. These are free strings and are often used to identify
the user/device the token was generated from/for, like 'Johns MacBook Air'.

```php
$routes->get('hmac/token', static function () {
    $token = auth()->user()->generateHmacToken(service('request')->getVar('token_name'));

    return json_encode(['key' => $token->secret, 'secretKey' => $token->rawSecretKey]);
});
```

You can access all the user's HMAC keys with the `hmacTokens()` method on that user.

```php
$tokens = $user->hmacTokens();
foreach ($tokens as $token) {
    //
}
```

### Usage

In order to use HMAC Keys/Token the `Authorization` header will be set to the following in the request:

```
Authorization: HMAC-SHA256 <key>:<HMAC HASH of request body>
```

The code to do this will look something like this:

```php
header("Authorization: HMAC-SHA256 {$key}:" . hash_hmac('sha256', $requestBody, $secretKey));
```

## HMAC Keys Permissions

HMAC keys can be given `scopes`, which are basically permission strings, for the HMAC Token/Keys. This is generally not
the same as the permission the user has, but is used to specify the permissions on the API itself. If not specified, the
token is granted all access to all scopes. This might be enough for a smaller API.

```php
$token = $user->generateHmacToken('token-name', ['users-read']);
return json_encode(['key' => $token->secret, 'secretKey' => $token->rawSecretKey]);
```

!!! note

    At this time, scope names should avoid using a colon (`:`) as this causes issues with the route filters being
    correctly recognized.

When handling incoming requests you can check if the token has been granted access to the scope with the `hmacTokenCan()` method.

```php
if ($user->hmacTokenCan('users-read')) {
    //
}
```

### Revoking Keys/Tokens

Tokens can be revoked by deleting them from the database with the `revokeHmacToken($key)` or `revokeAllHmacTokens()` methods.

```php
$user->revokeHmacToken($key);
$user->revokeAllHmacTokens();
```

## HMAC Secret Key Encryption

The HMAC Secret Key is stored encrypted. Before you start using HMAC, you will need to set/override the encryption key
in `$hmacEncryptionKeys` in **app/Config/AuthToken.php**. This should be set using **.env** and/or system
environment variables. Instructions on how to do that can be found in the
[Setting Your Encryption Key](https://codeigniter.com/user_guide/libraries/encryption.html#setting-your-encryption-key)
section of the CodeIgniter 4 documentation.

You will also be able to adjust the default Driver `$hmacEncryptionDefaultDriver` and the default Digest
`$hmacEncryptionDefaultDigest`, these default to `'OpenSSL'` and `'SHA512'` respectively.

See [HMAC SHA256 Token Authenticator](../references/authentication/hmac.md#hmac-secret-key-encryption) for additional
details on setting these values.

### Encryption Key Rotation

See [HMAC SHA256 Token Authenticator](../references/authentication/hmac.md#hmac-secret-key-encryption) for information on
how to set, rotate encryption keys and re-encrypt existing HMAC `'secretKey'` values.

## Protecting Routes

The first way to specify which routes are protected is to use the `hmac` controller filter.

For example, to ensure it protects all routes under the `/api` route group, you would use the `$filters` setting
on **app/Config/Filters.php**.

```php
public $filters = [
    'hmac' => ['before' => ['api/*']],
];
```

You can also specify the filter should run on one or more routes within the routes file itself:

```php
$routes->group('api', ['filter' => 'hmac'], function($routes) {
    //
});
$routes->get('users', 'UserController::list', ['filter' => 'hmac:users-read']);
```

When the filter runs, it checks the `Authorization` header for a `HMAC-SHA256` value that has the computed token. It then
parses the raw token and looks it up the `key` portion in the database. Once found, it will rehash the body of the request
to validate the remainder of the Authorization raw token.  If it passes the signature test it can determine the correct user,
which will then be available through an `auth()->user()` call.

!!! note

    Currently only a single scope can be used on a route filter. If multiple scopes are passed in, only the first one is checked.
