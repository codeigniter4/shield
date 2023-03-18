# Installation

- [Installation](#installation)
  - [Requirements](#requirements)
  - [Composer Installation](#composer-installation)
    - [Troubleshooting](#troubleshooting)
      - [IMPORTANT: composer error](#important-composer-error)
  - [Initial Setup](#initial-setup)
    - [Command Setup](#command-setup)
    - [Manual Setup](#manual-setup)
  - [Controller Filters](#controller-filters)
    - [Protect All Pages](#protect-all-pages)
    - [Rate Limiting](#rate-limiting)
    - [Forcing Password Reset](#forcing-password-reset)

These instructions assume that you have already [installed the CodeIgniter 4 app starter](https://codeigniter.com/user_guide/installation/installing_composer.html) as the basis for your new project, set up your **.env** file, and created a database that you can access via the Spark CLI script.

## Requirements

- [Composer](https://getcomposer.org)
- Codeigniter **v4.2.7** or later
- A created database that you can access via the Spark CLI script

## Composer Installation

Installation is done through [Composer](https://getcomposer.org). The example assumes you have it installed globally.
If you have it installed as a phar, or otherwise you will need to adjust the way you call composer itself.

```console
composer require codeigniter4/shield
```

### Troubleshooting

#### IMPORTANT: composer error

If you get the following error:

```console
Could not find a version of package codeigniter4/shield matching your minimum-stability (stable).
Require it with an explicit version constraint allowing its desired stability.
```

1. Run the following commands to change your [minimum-stability](https://getcomposer.org/doc/articles/versions.md#minimum-stability) in your project `composer.json`:

    ```console
    composer config minimum-stability dev
    composer config prefer-stable true
    ```

2. Or specify an explicit version:

    ```console
    composer require codeigniter4/shield:dev-develop
    ```

    The above specifies `develop` branch.
    See <https://getcomposer.org/doc/articles/versions.md#branches>

    ```console
    composer require codeigniter4/shield:^1.0.0-beta
    ```

    The above specifies `v1.0.0-beta` or later and before `v2.0.0`.
    See <https://getcomposer.org/doc/articles/versions.md#caret-version-range->

## Initial Setup

### Command Setup

1. Run the following command. This command handles steps 1-5 of *Manual Setup* and runs the migrations.

    ```console
    php spark shield:setup
    ```

    > **Note** If you want to customize table names, you must change the table names
    > before running database migrations.
    > See [Customizing Shield](./customization.md#custom-table-names).

2. Configure **app/Config/Email.php** to allow Shield to send emails with the [Email Class](https://codeigniter.com/user_guide/libraries/email.html).

    ```php
    <?php

    namespace Config;

    use CodeIgniter\Config\BaseConfig;

    class Email extends BaseConfig
    {
        /**
         * @var string
         */
        public $fromEmail = 'your_mail@example.com';

        /**
         * @var string
         */
        public $fromName = 'your name';

        // ...
    }
    ```

### Manual Setup

There are a few setup items to do before you can start using Shield in
your project.

1. Copy the **Auth.php** and  **AuthGroups.php** from **vendor/codeigniter4/shield/src/Config/** into your project's config folder and update the namespace to `Config`. You will also need to have these classes extend the original classes. See the example below. These files contain all of the settings, group, and permission information for your application and will need to be modified to meet the needs of your site.

    ```php
    // new file - app/Config/Auth.php
    <?php

    namespace Config;

    // ...
    use CodeIgniter\Shield\Config\Auth as ShieldAuth;

    class Auth extends ShieldAuth
    {
        // ...
    }
    ```

2. **Helper Setup** The `setting` helper needs to be included in almost every page. The simplest way to do this is to add it to the `BaseController::initController()` method:

    ```php
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->helpers = array_merge($this->helpers, ['setting']);

        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
    }
    ```

    This requires that all of your controllers extend the `BaseController`, but that's a good practice anyway.

3. **Routes Setup** The default auth routes can be setup with a single call in **app/Config/Routes.php**:

    ```php
    service('auth')->routes($routes);
    ```

4. **Security Setup** Set `Config\Security::$csrfProtection` to `'session'` (or set `security.csrfProtection = session` in your **.env** file) for security reasons, if you use Session Authenticator.

5. **Migration** Run the migrations.

    > **Note** If you want to customize table names, you must change the table names
    > before running database migrations.
    > See [Customizing Shield](./customization.md#custom-table-names).

    ```console
    php spark migrate --all
    ```

    #### Note: migration error

    When you run `spark migrate --all`, if you get `Class "SQLite3" not found` error:

    1. Remove sample migration files in **tests/_support/Database/Migrations/**
    2. Or install `sqlite3` php extension

    If you get `Specified key was too long` error:

    1. Use InnoDB, not MyISAM.

6. Configure **app/Config/Email.php** to allow Shield to send emails.

    ```php
    <?php

    namespace Config;

    use CodeIgniter\Config\BaseConfig;

    class Email extends BaseConfig
    {
        /**
         * @var string
         */
        public $fromEmail = 'your_mail@example.com';

        /**
         * @var string
         */
        public $fromName = 'your name';

        // ...
    }
    ```

## Controller Filters
The [Controller Filters](https://codeigniter.com/user_guide/incoming/filters.html) you can use to protect your routes the shield provides are:

```php
public $aliases = [
    // ...
    'session'    => \CodeIgniter\Shield\Filters\SessionAuth::class,
    'tokens'     => \CodeIgniter\Shield\Filters\TokenAuth::class,
    'chain'      => \CodeIgniter\Shield\Filters\ChainAuth::class,
    'auth-rates' => \CodeIgniter\Shield\Filters\AuthRates::class,
    'group'      => \CodeIgniter\Shield\Filters\GroupFilter::class,
    'permission' => \CodeIgniter\Shield\Filters\PermissionFilter::class,
    'force-reset' => \CodeIgniter\Shield\Filters\ForcePasswordResetFilter::class,
];
```

Filters | Description
--- | ---
session and tokens | The `Session` and `AccessTokens` authenticators, respectively.
chained | The filter will check both authenticators in sequence to see if the user is logged in through either of authenticators, allowing a single API endpoint to work for both an SPA using session auth, and a mobile app using access tokens.
auth-rates | Provides a good basis for rate limiting of auth-related routes.
group | Checks if the user is in one of the groups passed in.
permission | Checks if the user has the passed permissions.
force-reset | Checks if the user requires a password reset.

These can be used in any of the [normal filter config settings](https://codeigniter.com/user_guide/incoming/filters.html#globals), or [within the routes file](https://codeigniter.com/user_guide/incoming/routing.html#applying-filters).

> **Note** These filters are already loaded for you by the registrar class located at **src/Config/Registrar.php**.

### Protect All Pages

If you want to limit all routes (e.g. `localhost:8080/admin`, `localhost:8080/panel` and ...), you need to add the following code in the **app/Config/Filters.php** file.

```php
public $globals = [
    'before' => [
        // ...
        'session' => ['except' => ['login*', 'register', 'auth/a/*']],
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

> **Note** If you have grouped or changed the default format of the routes, ensure that your code matches the new format(s) in the **app/Config/Filter.php** file.

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
        'session' => ['except' => ['accounts/login*', 'accounts/register', 'accounts/auth/a/*']]
    ]
]
```
The same should apply for the Rate Limiting and Forcing Password Reset.
