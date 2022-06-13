# Installation 

Installation is done through [Composer](https://getcomposer.org). The example assumes you have it installed globally. 
If you have it installed as a phar, or othewise you will need to adjust the way you call composer itself.

```
> composer require codeigniter4/shield
```

---

**IMPORTANT**: If you get the following error:

```
  Could not find a version of package codeigniter4/shield matching your minimum-stability (stable).
  Require it with an explicit version constraint allowing its desired stability.
```

1. Add the following to change your [minimum-stability](https://getcomposer.org/doc/articles/versions.md#minimum-stability) in your project `composer.json`:

```
    "minimum-stability": "dev",
    "prefer-stable": true,
```

2. Or specify an explicit version:

```
> composer require codeigniter4/shield:dev-develop
```

The above specifies `develop` branch.
See https://getcomposer.org/doc/articles/versions.md#branches

```
> composer require codeigniter4/shield:^1.0.0-beta
```

The above specifies `v1.0.0-beta` or later and before `v2.0.0`.
See https://getcomposer.org/doc/articles/versions.md#caret-version-range-

---

This requires the [CodeIgniter Settings](https://github.com/codeigniter4/settings) package, which uses a database
table to store configuration options. As such, you should run the migrations. 

```
> php spark migrate --all
```

---

#### **Note**:

When you run `spark migrate --all`, if you get `Class "SQLite3" not found` error:

1. Remove sample migration files in `tests/_support/Database/Migrations/`
2. Or install `sqlite3` php extension

---

## Initial Setup

There are a few setup items to do before you can start using Shield in 
your project. 

1. Copy the `Auth.php` and  `AuthGroups.php` from `vendor/codeigniter4/shield/src/Config/` into your project's config folder and update the namespace to `Config`. You will also need to have these classes extend the original classes. See the example below. These files contain all of the settings, group, and permission information for your application and will need to be modified to meet the needs of your site.

```php
// new file - app/Config/Auth.php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Sparks\Shield\Authentication\Handlers\AccessTokens;
use Sparks\Shield\Authentication\Handlers\Session;
use Sparks\Shield\Config\Auth as ShieldAuth;

class Auth extends ShieldAuth
{
    //
}
```

2. **Helper Setup** The `auth` and `setting` helpers need to be included in almost every page. The simplest way to do this is to add them to the `BaseController::initController` method: 

```php
public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
{
    $this->helpers = array_merge($this->helpers, ['auth', 'setting']);

    // Do Not Edit This Line
    parent::initController($request, $response, $logger);
}
```

This requires that all of your controllers extend the `BaseController`, but that's a good practice anyway.

3. **Routes Setup** The default auth routes can be setup with a single call in `app/Config/Routes.php`: 

```php
service('auth')->routes($routes);
```

4. (If you are running CodeIgniter v4.2.0 or higher you can skip this step). Add the new password validation rules
by editing `app/Config/Validation.php`: 

```php
use Sparks\Shield\Authentication\Passwords\ValidationRules as PasswordRules;

public $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        PasswordRules::class     // <!-- add this line
    ];
```

## Further Customization

### Route Configuration

If you need to customize how any of the auth features are handled, you will likely need to update the routes to point to the correct controllers. You can still use the `service('auth')->routes()` helper, but you will need to pass the `except` option with a list of routes to customize:

```php
service('auth')->routes($routes, ['except' => ['login', 'register']]);
```

Then add the routes to your customized controllers:

```php
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');
$routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
```

### Extending the Controllers

Shield has the following controllers that can be extended to handle 
various parts of the authentication process: 

- **ActionController** handles the after login and after-registration actions that can be ran, like Two Factor Authentication and Email Verification. 

- **LoginController** handles the login process.

- **RegisterController** handles the registration process. Overriding this class allows you to customize the User Provider, the User Entity, and the validation rules.

- **MagicLinkController** handles the "lost password" process that allows a user to login with a link sent to their email. Allows you to
override the message that is displayed to a user to describe what is happening, if you'd like to provide more information than simply swapping out the view used.

It is not recommended to copy the entire controller into app and change it's namespace. Instead, you should create a new controller that extends
the existing controller and then only override the methods needed. This allows the other methods to always stay up to date with any security 
updates that might happen in the controllers.

```php
<?php

namespace App\Controllers;

use Sparks\Shield\Controllers\LoginController as ShieldLogin;

class LoginController extends ShieldLogin
{
    public function logoutAction()
    {
        // new functionality 
    }
}
```
