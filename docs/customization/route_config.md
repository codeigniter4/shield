# Customizing Routes

If you need to customize how any of the auth features are handled, you will likely need to update the routes to point to the correct controllers. You can still use the `service('auth')->routes()` helper, but you will need to pass the `except` option with a list of routes to customize:

```php
service('auth')->routes($routes, ['except' => ['login', 'register']]);
```

Then add the routes to your customized controllers:

```php
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');
$routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
```
