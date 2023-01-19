# Protecting an API with Access Tokens

Access Tokens can be used to authenticate users for your own site, or when allowing third-party developers to access your API. When making requests using access tokens, the token should be included in the `Authorization` header as a `Bearer` token.

> **Note**  By default, `$authenticatorHeader['tokens']` is set to `Authorization`. You can change this value by setting the `$authenticatorHeader['tokens']` value in the **app/Config/Auth.php** config file.

Tokens are issued with the `generateAccessToken()` method on the user. This returns a `CodeIgniter\Shield\Entities\AccessToken` instance. Tokens are hashed using a SHA-256 algorithm before being saved to the database. The access token returned when you generate it will include a `raw_token` field that contains the plain-text, un-hashed, token. You should display this to your user at once so they have a chance to copy it somewhere safe, as this is the only time this will be available. After this request, there is no way to get the raw token.

The `generateAccessToken()` method requires a name for the token. These are free strings and are often used to identify the user/device the token was generated from, like 'Johns MacBook Air'.

```php
$routes->get('/access/token', static function() {
    $token = auth()->user()->generateAccessToken(service('request')->getVar('token_name'));

    return json_encode(['token' => $token->raw_token]);
});
```

You can access all of the user's tokens with the `accessTokens()` method on that user.

```php
$tokens = $user->accessTokens();
foreach($tokens as $token) {
    //
}
```

## Token Permissions

Access tokens can be given `scopes`, which are basically permission strings, for the token. This is generally not the same as the permission the user has, but is used to specify the permissions on the API itself. If not specified, the token is granted all access to all scopes. This might be enough for a smaller API.

```php
return $user->generateAccessToken('token-name', ['users-read'])->raw_token;
```

> **Note**
> At this time, scope names should avoid using a colon (`:`) as this causes issues with the route filters being correctly recognized.

When handling incoming requests you can check if the token has been granted access to the scope with the `tokenCan()` method.

```php
if ($user->tokenCan('users-read')) {
    //
}
```

### Revoking Tokens

Tokens can be revoked by deleting them from the database with the `revokeAccessToken($rawToken)` or `revokeAllAccessTokens()` methods.

```php
$user->revokeAccessToken($rawToken);
$user->revokeAllAccessTokens();
```

## Protecting Routes

The first way to specify which routes are protected is to use the `tokens` controller filter.

For example, to ensure it protects all routes under the `/api` route group, you would use the `$filters` setting on **app/Config/Filters.php**.

```php
public $filters = [
    'tokens' => ['before' => ['api/*']],
];
```

You can also specify the filter should run on one or more routes within the routes file itself:

```php
$routes->group('api', ['filter' => 'tokens'], function($routes) {
    //
});
$routes->get('users', 'UserController::list', ['filter' => 'tokens:users-read']);
```

When the filter runs, it checks the `Authorization` header for a `Bearer` value that has the raw token. It then hashes the raw token and looks it up in the database. Once found, it can determine the correct user, which will then be available through an `auth()->user()` call.

> **Note**
> Currently only a single scope can be used on a route filter. If multiple scopes are passed in, only the first one is checked.
