# Customizing Routes

## Change Some Routes

If you need to customize how any of the auth features are handled, you will likely need to update the routes to point to the correct controllers.

You can still use the `service('auth')->routes()` helper, but you will need to pass the `except` option with a list of routes to customize:

```php
service('auth')->routes($routes, ['except' => ['login', 'register']]);
```

Then add the routes to your customized controllers:

```php
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');
$routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
```

After customization, check your routes with the [spark routes](https://codeigniter.com/user_guide/incoming/routing.html#spark-routes) command.

## Use Locale Routes

You can use the `{locale}` placeholder in your routes
(see [Locale Detection](https://codeigniter.com/user_guide/outgoing/localization.html#in-routes)).

```php
$routes->group('{locale}', static function($routes) {
    service('auth')->routes($routes);
});
```

The above code registers the following routes:

```text
+--------+----------------------------------+--------------------+--------------------------------------------------------------------+----------------+---------------+
| Method | Route                            | Name               | Handler                                                            | Before Filters | After Filters |
+--------+----------------------------------+--------------------+--------------------------------------------------------------------+----------------+---------------+
| GET    | {locale}/register                | register           | \CodeIgniter\Shield\Controllers\RegisterController::registerView   |                | toolbar       |
| GET    | {locale}/login                   | login              | \CodeIgniter\Shield\Controllers\LoginController::loginView         |                | toolbar       |
| GET    | {locale}/login/magic-link        | magic-link         | \CodeIgniter\Shield\Controllers\MagicLinkController::loginView     |                | toolbar       |
| GET    | {locale}/login/verify-magic-link | verify-magic-link  | \CodeIgniter\Shield\Controllers\MagicLinkController::verify        |                | toolbar       |
| GET    | {locale}/logout                  | logout             | \CodeIgniter\Shield\Controllers\LoginController::logoutAction      |                | toolbar       |
| GET    | {locale}/auth/a/show             | auth-action-show   | \CodeIgniter\Shield\Controllers\ActionController::show             |                | toolbar       |
| POST   | {locale}/register                | register           | \CodeIgniter\Shield\Controllers\RegisterController::registerAction |                | toolbar       |
| POST   | {locale}/login                   | »                  | \CodeIgniter\Shield\Controllers\LoginController::loginAction       |                | toolbar       |
| POST   | {locale}/login/magic-link        | »                  | \CodeIgniter\Shield\Controllers\MagicLinkController::loginAction   |                | toolbar       |
| POST   | {locale}/auth/a/handle           | auth-action-handle | \CodeIgniter\Shield\Controllers\ActionController::handle           |                | toolbar       |
| POST   | {locale}/auth/a/verify           | auth-action-verify | \CodeIgniter\Shield\Controllers\ActionController::verify           |                | toolbar       |
+--------+----------------------------------+--------------------+--------------------------------------------------------------------+----------------+---------------+
```

If you set the global filter in the **app/Config/Filters.php** file, you need to
update the paths for `except`:

```php
public $globals = [
    'before' => [
        // ...
        'session' => ['except' => ['*/login*', '*/register', '*/auth/a/*', '*/logout']],
    ],
    // ...
];
```
