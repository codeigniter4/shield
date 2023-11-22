# Controller Filters

## Provided Filters

!!! note

    The filter `$aliases` that Shield provides are automatically added for you by the
    [Registrar](https://codeigniter.com/user_guide/general/configuration.html#registrars)
    class located at **src/Config/Registrar.php**. So you don't need to add in
    your **app/Config/Filters.php**.

The [Controller Filters](https://codeigniter.com/user_guide/incoming/filters.html) you can use to protect your routes Shield provides are:

```php
$aliases = [
    'session'     => \CodeIgniter\Shield\Filters\SessionAuth::class,
    'tokens'      => \CodeIgniter\Shield\Filters\TokenAuth::class,
    'hmac'        => \CodeIgniter\Shield\Filters\HmacAuth::class,
    'chain'       => \CodeIgniter\Shield\Filters\ChainAuth::class,
    'auth-rates'  => \CodeIgniter\Shield\Filters\AuthRates::class,
    'group'       => \CodeIgniter\Shield\Filters\GroupFilter::class,
    'permission'  => \CodeIgniter\Shield\Filters\PermissionFilter::class,
    'force-reset' => \CodeIgniter\Shield\Filters\ForcePasswordResetFilter::class,
    'jwt'         => \CodeIgniter\Shield\Filters\JWTAuth::class,
];
```

| Filters     | Description                                                                                                                                                                                                                         |
|-------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| session     | The `Session` authenticator.                                                                                                                                                                                                        |
| tokens      | The `AccessTokens` authenticator.                                                                                                                                                                                                   |
| chained     | The filter will check authenticators in sequence to see if the user is logged in through either of authenticators, allowing a single API endpoint to work for both an SPA using session auth, and a mobile app using access tokens. |
| jwt         | The `JWT` authenticator. See [JWT Authentication](../addons/jwt.md).                                                                                                                                                                |
| hmac        | The `HMAC` authenticator. See [HMAC Authentication](../guides/api_hmac_keys.md).                                                                                                                                                    |
| auth-rates  | Provides a good basis for rate limiting of auth-related routes.                                                                                                                                                                     |
| group       | Checks if the user is in one of the groups passed in.                                                                                                                                                                               |
| permission  | Checks if the user has the passed permissions.                                                                                                                                                                                      |
| force-reset | Checks if the user requires a password reset.                                                                                                                                                                                       |

These can be used in any of the [normal filter config settings](https://codeigniter.com/user_guide/incoming/filters.html#globals), or [within the routes file](https://codeigniter.com/user_guide/incoming/routing.html#applying-filters).

## Configure Controller Filters

### Protect All Pages

If you want to limit all routes (e.g. `localhost:8080/admin`, `localhost:8080/panel` and ...), you need to add the following code in the **app/Config/Filters.php** file.

```php
public $globals = [
    'before' => [
        // ...
        'session' => ['except' => ['login*', 'register', 'auth/a/*', 'logout']],
    ],
    // ...
];
```

### Rate Limiting

To help protect your authentication forms from being spammed by bots, it is recommended that you use
the `auth-rates` filter on all of your authentication routes. This can be done with the following
filter setup:

```php
public $filters = [
    'auth-rates' => [
        'before' => [
            'login*', 'register', 'auth/*'
        ]
    ]
];
```

### Forcing Password Reset

If your application requires a force password reset functionality, ensure that you exclude the auth pages and the actual password reset page from the `before` global. This will ensure that your users do not run into a *too many redirects* error. See:

```php
public $globals = [
    'before' => [
        //...
        //...
        'force-reset' => ['except' => ['login*', 'register', 'auth/a/*', 'change-password', 'logout']]
    ]
];
```
In the example above, it is assumed that the page you have created for users to change their password after successful login is **change-password**.

!!! note

    If you have grouped or changed the default format of the routes, ensure that your code matches the new format(s) in the **app/Config/Filter.php** file.

For example, if you configured your routes like so:

```php
$routes->group('accounts', static function($routes) {
    service('auth')->routes($routes);
});
```

Then the global `before` filter for `session` should look like so:

```php
public $globals = [
    'before' => [
        // ...
        'session' => ['except' => ['accounts/login*', 'accounts/register', 'accounts/auth/a/*', 'accounts/logout']]
    ]
]
```

The same should apply for the Rate Limiting and Forcing Password Reset.
