# Using Session Authenticator

**Session** authenticator provides traditional ID/Password authentication.

Learning any new authentication system can be difficult, especially as they get more flexible and sophisticated. This guide is intended to provide short examples for common actions you'll take when working with Shield. It is not intended to be the exhaustive documentation for each section. That's better handled through the area-specific doc files.

!!! note

    The examples assume that you have run the setup script and that you have copies of the `Auth` and `AuthGroups` config files in your application's **app/Config** folder.

## Configuration

### Configure Redirect URLs

If you need everyone to redirect to a single URL after login/logout/register actions, you can modify the `Config\Auth::$redirects` array in **app/Config/Auth.php** to specify the url to redirect to.

By default, a successful login or register attempt will all redirect to `/`, while a logout action
will redirect to a [named route](https://codeigniter.com/user_guide/incoming/routing.html#using-named-routes "See routing docs") `login` or a *URI path* `/login`. You can change the default URLs used within the **app/Config/Auth.php** config file:

```php
public array $redirects = [
    'register' => '/',
    'login'    => '/',
    'logout'   => 'login',
];
```

!!! note

    This redirect happens after the specified action is complete. In the case of register or login, it might not happen immediately. For example, if you have any Auth Actions specified, they will be redirected when those actions are completed successfully. If no Auth Actions are specified, they will be redirected immediately after registration or login.

### Configure Remember-me Functionality

Remember-me functionality is enabled by default. While this is handled in a secure manner, some sites may want it disabled. You might also want to change how long it remembers a user and doesn't require additional login.

```php
public array $sessionConfig = [
    'field'              => 'user',
    'allowRemembering'   => true,
    'rememberCookieName' => 'remember',
    'rememberLength'     => 30 * DAY,
];
```

### Enable Account Activation via Email

!!! note

    You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](../getting_started/install.md#initial-setup).

By default, once a user registers they have an active account that can be used. You can enable Shield's built-in, email-based activation flow within the `Auth` config file.

```php
public array $actions = [
    'register' => \CodeIgniter\Shield\Authentication\Actions\EmailActivator::class,
    'login'    => null,
];
```

### Enable Two-Factor Authentication

!!! note

    You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](../getting_started/install.md#initial-setup).

Turned off by default, Shield's Email-based 2FA can be enabled by specifying the class to use in the `Auth` config file.

```php
public array $actions = [
    'register' => null,
    'login'    => \CodeIgniter\Shield\Authentication\Actions\Email2FA::class,
];
```

## Customizing Routes

If you need to customize how any of the auth features are handled, you can still
use the `service('auth')->routes()` helper, but you will need to pass the `except`
option with a list of routes to customize:

```php
service('auth')->routes($routes, ['except' => ['login', 'register']]);
```

Then add the routes to your customized controllers:

```php
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');
$routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
```

Check your routes with the [spark routes](https://codeigniter.com/user_guide/incoming/routing.html#spark-routes)
command.

## Protecting Pages

By default, Shield does not protect pages. To make certain pages accessible only
to logged-in users, set the `session`
[controller filter](../references/controller_filters.md).

For example, if you want to limit all routes (e.g. `localhost:8080/admin`,
`localhost:8080/panel` and ...), you need to add the following code in the
**app/Config/Filters.php** file.

```php
public $globals = [
    'before' => [
        // ...
        'session' => ['except' => ['login*', 'register', 'auth/a/*', 'logout']],
    ],
    // ...
];
```

!!! note

    The filter `$aliases` that Shield provides are automatically added for you by the
    [Registrar](https://codeigniter.com/user_guide/general/configuration.html#registrars)
    class located at **src/Config/Registrar.php**. So you don't need to add in
    your **app/Config/Filters.php**.

Check your filters with the [spark routes](https://codeigniter.com/user_guide/incoming/routing.html#spark-routes)
command.
