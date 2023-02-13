# Customizing Shield

- [Customizing Shield](#customizing-shield)
  - [Custom Table Names](#custom-table-names)
  - [Route Configuration](#route-configuration)
  - [Custom Redirect URLs](#custom-redirect-urls)
    - [Customize Login Redirect](#customize-login-redirect)
    - [Customize Register Redirect](#customize-register-redirect)
    - [Customize Logout Redirect](#customize-logout-redirect)
  - [Extending the Controllers](#extending-the-controllers)
  - [Integrating Custom View Libraries](#integrating-custom-view-libraries)
  - [Custom Validation Rules](#custom-validation-rules)
    - [Registration](#registration)
    - [Login](#login)
  - [Custom User Provider](#custom-user-provider)
  - [Custom Login Identifier](#custom-login-identifier)

## Custom Table Names

If you want to change the default table names, you can change the table names
in **app/Config/Auth.php**.

```php
public array $tables = [
    'users'             => 'users',
    'identities'        => 'auth_identities',
    'logins'            => 'auth_logins',
    'token_logins'      => 'auth_token_logins',
    'remember_tokens'   => 'auth_remember_tokens',
    'groups_users'      => 'auth_groups_users',
    'permissions_users' => 'auth_permissions_users',
];
```

Set the table names that you want in the array values.

> **Note** You must change the table names before running database migrations.

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

### Customize Login Redirect

You can customize where a user is redirected to on login with the `loginRedirect()` method of the **app/Config/Auth.php** config file. This is handy if you want to redirect based on user group or other criteria.

```php
public function loginRedirect(): string
{
    $url = auth()->user()->inGroup('admin')
        ? '/admin'
        : setting('Auth.redirects')['login'];

    return $this->getUrl($url);
}
```

Oftentimes, you will want to have different redirects for different user groups. A simple example
might be that you want admins redirected to `/admin` while all other groups redirect to `/`.
The **app/Config/Auth.php** config file also includes methods that you can add additional logic to in order to
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

### Customize Register Redirect

You can customize where a user is redirected to after registration in the `registerRedirect()` method of the **app/Config/Auth.php** config file.

```php
public function registerRedirect(): string
{
    $url = setting('Auth.redirects')['register'];

    return $this->getUrl($url);
}
```

### Customize Logout Redirect

The logout redirect can also be overridden by the `logoutRedirect()` method of the **app/Config/Auth.php** config file. This will not be used as often as login and register, but you might find the need. For example, if you programatically logged a user out you might want to take them to a page that specifies why they were logged out. Otherwise, you might take them to the home page or even the login page.

```php
public function logoutRedirect(): string
{
    $url = setting('Auth.redirects')['logout'];

    return $this->getUrl($url);
}
```

## Extending the Controllers

Shield has the following controllers that can be extended to handle
various parts of the authentication process:

-   **ActionController** handles the after-login and after-registration actions, like Two Factor Authentication and Email Verification.
-   **LoginController** handles the login process.
-   **RegisterController** handles the registration process. Overriding this class allows you to customize the User Provider, the User Entity, and the validation rules.
-   **MagicLinkController** handles the "lost password" process that allows a user to login with a link sent to their email. This allows you to
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

## Integrating Custom View Libraries

If your application uses a different method to convert view files to HTML than CodeIgniter's built-in `view()` helper you can easily integrate your system anywhere that a view is rendered within Shield. All controllers and actions use the `CodeIgniter\Shield\Traits\Viewable` trait which provides a simple `view()` method that takes the same arguments as the `view()` helper. This allows you to extend the Action or Controller and only change the single method of rendering the view, leaving all of the logic untouched so your app will not need to maintain Shield logic when it doesn't need to change it.

```php
use Acme\Themes\Traits\Themeable;
use CodeIgniter\Shield\Controllers\LoginController;

class MyLoginController extends LoginController
{
    use Themable;

    protected function view(string $view, array $data = [], array $options = []): string
    {
        return $this->themedView($view, $data, $options);
    }
}
```

## Custom Validation Rules

### Registration

Shield has the following rules for registration:

```php
[
    'username' => [
        'label' => 'Auth.username',
        'rules' => [
            'required',
            'max_length[30]',
            'min_length[3]',
            'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
            'is_unique[users.username]',
        ],
    ],
    'email' => [
        'label' => 'Auth.email',
        'rules' => [
            'required',
            'max_length[254]',
            'valid_email',
            'is_unique[auth_identities.secret]',
        ],
    ],
    'password' => [
        'label' => 'Auth.password',
        'rules' => 'required|strong_password',
    ],
    'password_confirm' => [
        'label' => 'Auth.passwordConfirm',
        'rules' => 'required|matches[password]',
    ],
];
```

> **Note** If you customize the table names, the table names
> (`users` and `auth_identities`) in the above rules will be automatically
> changed. The rules are implemented in
> `RegisterController::getValidationRules()`.

If you need a different set of rules for registration, you can specify them in your `Validation` configuration (**app/Config/Validation.php**) like:

```php
    //--------------------------------------------------------------------
    // Rules For Registration
    //--------------------------------------------------------------------
    public $registration = [
        'username' => [
            'label' => 'Auth.username',
            'rules' => [
                'required',
                'max_length[30]',
                'min_length[3]',
                'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
                'is_unique[users.username]',
            ],
        ],
        'email' => [
            'label' => 'Auth.email',
            'rules' => [
                'required',
                'max_length[254]',
                'valid_email',
                'is_unique[auth_identities.secret]',
            ],
        ],
        'password' => [
            'label' => 'Auth.password',
            'rules' => 'required|strong_password',
        ],
        'password_confirm' => [
            'label' => 'Auth.passwordConfirm',
            'rules' => 'required|matches[password]',
        ],
    ];
```

> **Note** If you customize the table names, set the correct table names in the
> rules.

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

## Custom User Provider

If you want to customize user attributes, you need to create your own
[User Provider](./concepts.md#user-providers) class.
The only requirement is that your new class MUST extend the provided `CodeIgniter\Shield\Models\UserModel`.

Shield has a CLI command to quickly create a custom `UserModel` class by running the following
command in the terminal:

```console
php spark shield:model UserModel
```

The class name is optional. If none is provided, the generated class name would be `UserModel`.

After creating the class, set the `$userProvider` property in **app/Config/Auth.php** as follows:

```php
public string $userProvider = \App\Models\UserModel::class;
```

## Custom Login Identifier

If your application has a need to use something other than `email` or `username`, you may specify any valid column within the `users` table that you may have added. This allows you to easily use phone numbers, employee or school IDs, etc as the user identifier. You must implement the following steps to set this up:

This only works with the Session authenticator.

1. Create a [migration](http://codeigniter.com/user_guide/dbmgmt/migration.html) that adds a new column to the `users` table.
2. Edit `app/Config/Auth.php` so that the new column you just created is within the `$validFields` array.

    ```php
    public array $validFields = [
        'employee_id'
    ];
    ```

    If you have multiple login forms on your site that use different credentials, you must have all of the valid identifying fields in the array.

    ```php
    public array $validFields = [
        'email',
        'employee_id'
    ];
    ```
    > **Warning**
    > It is very important for security that if you add a new column for identifier you must write a new **Validation Rules** and then set it using the [custom-validation-rules](https://github.com/codeigniter4/shield/blob/develop/docs/customization.md#custom-validation-rules) description.

3. Edit the login form to change the name of the default `email` input to the new field name.

    ```php
    <!-- Email -->
    <div class="mb-2">
        <input type="text" class="form-control" name="employee_id" autocomplete="new-employee-id" placeholder="12345" value="<?= old('employee_id') ?>" required />
    </div>
    ```
