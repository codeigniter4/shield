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

These instructions assume that you have already [installed the CodeIgniter 4 app starter](https://codeigniter.com/user_guide/installation/installing_composer.html) as the basis for your new project, set up your `.env` file, and created a database that you can access via the Spark CLI script.

## Requirements

- [Composer](https://getcomposer.org)
- Codeigniter **v4.2.3** or later
- A created database that you can access via the Spark CLI script

## Composer Installation

Installation is done through [Composer](https://getcomposer.org). The example assumes you have it installed globally.
If you have it installed as a phar, or otherwise you will need to adjust the way you call composer itself.

```
> composer require codeigniter4/shield
```

### Troubleshooting

#### IMPORTANT: composer error

If you get the following error:

```
  Could not find a version of package codeigniter4/shield matching your minimum-stability (stable).
  Require it with an explicit version constraint allowing its desired stability.
```

1. Add the following to change your [minimum-stability](https://getcomposer.org/doc/articles/versions.md#minimum-stability) in your project `composer.json`:

    ```json
        "minimum-stability": "dev",
        "prefer-stable": true,
    ```

2. Or specify an explicit version:

    ```console
    > composer require codeigniter4/shield:dev-develop
    ```

    The above specifies `develop` branch.
    See https://getcomposer.org/doc/articles/versions.md#branches

    ```console
    > composer require codeigniter4/shield:^1.0.0-beta
    ```

    The above specifies `v1.0.0-beta` or later and before `v2.0.0`.
    See https://getcomposer.org/doc/articles/versions.md#caret-version-range-

## Initial Setup

### Command Setup

1. Run the following command. This command handles steps 1-5 of *Manual Setup* and runs the migrations.

    ```console
    > php spark shield:setup
    ```

### Manual Setup

There are a few setup items to do before you can start using Shield in
your project.

1. Copy the `Auth.php` and  `AuthGroups.php` from `vendor/codeigniter4/shield/src/Config/` into your project's config folder and update the namespace to `Config`. You will also need to have these classes extend the original classes. See the example below. These files contain all of the settings, group, and permission information for your application and will need to be modified to meet the needs of your site.

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

2. **Helper Setup** The `setting` helper needs to be included in almost every page. The simplest way to do this is to add it to the `BaseController::initController` method:

    ```php
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->helpers = array_merge($this->helpers, ['setting']);

        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
    }
    ```

    This requires that all of your controllers extend the `BaseController`, but that's a good practice anyway.

3. **Routes Setup** The default auth routes can be setup with a single call in `app/Config/Routes.php`:

    ```php
    service('auth')->routes($routes);
    ```

4. **Security Setup** Set `Config\Security::$csrfProtection` to `'session'` (or set `security.csrfProtection = session` in your `.env` file) for security reasons, if you use Session Authenticator.

5. **Migration** Run the migrations.

    ```console
    > php spark migrate --all
    ```

    #### Note: migration error

    When you run `spark migrate --all`, if you get `Class "SQLite3" not found` error:

    1. Remove sample migration files in `tests/_support/Database/Migrations/`
    2. Or install `sqlite3` php extension

    If you get `Specified key was too long` error:

    1. Use InnoDB, not MyISAM.

## Controller Filters

Shield provides 4 [Controller Filters](https://codeigniter.com/user_guide/incoming/filters.html) you can
use to protect your routes, `session`, `tokens`, and `chained`. The first two cover the `Session` and
`AccessTokens` authenticators, respectively. The `chained` filter will check both authenticators in sequence
to see if the user is logged in through either of authenticators, allowing a single API endpoint to
work for both an SPA using session auth, and a mobile app using access tokens. The fourth, `auth-rates`,
provides a good basis for rate limiting of auth-related routes.
These can be used in any of the [normal filter config settings](https://codeigniter.com/user_guide/incoming/filters.html?highlight=filter#globals), or [within the routes file](https://codeigniter.com/user_guide/incoming/routing.html?highlight=routs#applying-filters).

### Protect All Pages

If you want to limit all routes (e.g. `localhost:8080/admin`, `localhost:8080/panel` and ...), you need to add the following code in the `app/Config/Filters.php` file.

```php
public $globals = [
    'before' => [
        // ...
        'session' => ['except' => ['login*', 'register', 'auth/a/*']],
    ],
    // ...
];
```

> **Note** These filters are already loaded for you by the registrar class located at `src/Config/Registrar.php`.

```php
public $aliases = [
    // ...
    'session'    => \CodeIgniter\Shield\Filters\SessionAuth::class,
    'tokens'     => \CodeIgniter\Shield\Filters\TokenAuth::class,
    'chain'      => \CodeIgniter\Shield\Filters\ChainAuth::class,
    'auth-rates' => \CodeIgniter\Shield\Filters\AuthRates::class,
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
