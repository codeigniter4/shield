# Customizing Shield

- [Customizing Shield](#customizing-shield)
  - [Route Configuration](#route-configuration)
  - [Custom Redirect URLs](#custom-redirect-urls)
  - [Extending the Controllers](#extending-the-controllers)
  - [Custom Validation Rules](#custom-validation-rules)
    - [Registration](#registration)
    - [Login](#login)

## Route Configuration

If you need to customize how any of the auth features are handled, you will likely need to update the routes to point to the correct controllers. You can still use the `service('auth')->routes()` helper, but you will need to pass the `except` option with a list of routes to customize:

```php
service('auth')->routes($routes, ['except' => ['login', 'register']]);
```

Then add the routes to your customized controllers:

```php
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');
$routes->get('register', '\App\Controllers\Auth\RegisterController::registerView');
```



## Custom Redirect URLs

By default, a successful login or register attempt will all redirect to `/`, while a logout action
will redirect to `/login`. You can change the default URLs used within the `Auth` config file:

```php
public array $redirects = [
    'register' => '/',
    'login'    => '/',
    'logout'   => 'login',
];
```

Oftentimes, you will want to have different redirects for different user groups. A simple example
might be that you want admins redirected to `/admin` while all other groups redirect to `/`.
The `Auth` config file also includes methods that you can add additional logic to in order to
achieve this:

```php
public function loginRedirect(): string
{
    if (auth()->user()->can('admin.access')) {
        return '/admin';
    }

    $url = setting('Auth.redirects')['login'];

    return $this->getUrl($url);
}
```

## Extending the Controllers

Shield has the following controllers that can be extended to handle
various parts of the authentication process:

- **ActionController** handles the after-login and after-registration actions, like Two Factor Authentication and Email Verification.

- **LoginController** handles the login process.

- **RegisterController** handles the registration process. Overriding this class allows you to customize the User Provider, the User Entity, and the validation rules.

- **MagicLinkController** handles the "lost password" process that allows a user to login with a link sent to their email. This allows you to
override the message that is displayed to a user to describe what is happening, if you'd like to provide more information than simply swapping out the view used.

It is not recommended to copy the entire controller into **app/Controllers** and change its namespace. Instead, you should create a new controller that extends
the existing controller and then only override the methods needed. This allows the other methods to stay up to date with any security
updates that might happen in the controllers.

```php
<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\LoginController as ShieldLogin;

class LoginController extends ShieldLogin
{
    public function logoutAction()
    {
        // new functionality
    }
}
```

## Custom Validation Rules

### Registration

Shield has the following rules for registration:

```php
[
    'username' => [
        'label' =>  'Auth.username',
        'rules' => 'required|max_length[30]|min_length[3]|regex_match[/\A[a-zA-Z0-9\.]+\z/]|is_unique[users.username]',
    ],
    'email' => [
        'label' =>  'Auth.email',
        'rules' => 'required|max_length[254]|valid_email|is_unique[auth_identities.secret]',
    ],
    'password' => [
        'label' =>  'Auth.password',
        'rules' => 'required|strong_password',
    ],
    'password_confirm' => [
        'label' =>  'Auth.passwordConfirm',
        'rules' => 'required|matches[password]',
    ],
];
```

If you need a different set of rules for registration, you can specify them in your `Validation` configuration (**app/Config/Validation.php**) like:

```php
    //--------------------------------------------------------------------
    // Rules For Registration
    //--------------------------------------------------------------------
    public $registration = [
        'username' => [
            'label' =>  'Auth.username',
            'rules' => 'required|max_length[30]|min_length[3]|regex_match[/\A[a-zA-Z0-9\.]+\z/]|is_unique[users.username]',
        ],
        'email' => [
            'label' =>  'Auth.email',
            'rules' => 'required|max_length[254]|valid_email|is_unique[auth_identities.secret]',
        ],
        'password' => [
            'label' =>  'Auth.password',
            'rules' => 'required|strong_password',
        ],
        'password_confirm' => [
            'label' =>  'Auth.passwordConfirm',
            'rules' => 'required|matches[password]',
        ],
    ];
```

### Login

Similar to the process for validation rules in the **Registration** section, you can add rules for the login form to **app/Config/Validation.php** and change the rules.

```php
    //--------------------------------------------------------------------
    // Rules For Login 
    //--------------------------------------------------------------------
    public $login = [
        // 'username' => [
        //     'label' =>  'Auth.username',
        //     'rules' => 'required|max_length[30]|min_length[3]|regex_match[/\A[a-zA-Z0-9\.]+\z/]',
        // ],
        'email' => [
            'label' =>  'Auth.email',
            'rules' => 'required|max_length[254]|valid_email',
        ],
        'password' => [
            'label' =>  'Auth.password',
            'rules' => 'required',
        ],
    ];
```
