# Installation

These instructions assume that you have already [installed the CodeIgniter 4 app starter](https://codeigniter.com/user_guide/installation/installing_composer.html) as the basis for your new project, set up your **.env** file, and created a database that you can access via the Spark CLI script.

## Requirements

- [Composer](https://getcomposer.org)
- Codeigniter **v4.3.5** or later
- A created database that you can access via the Spark CLI script
  - InnoDB (not MyISAM) is required if MySQL is used.

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

There are a few setup items to do before you can start using Shield in
your project.

### Command Setup

1. Run the following command. This command handles steps 1-6 of *Manual Setup*.

    ```console
    php spark shield:setup
    ```

    !!! note

        If you want to customize table names, you must change the table names before running database migrations.
        See [Customizing Table Names](../customization/table_names.md).

### Manual Setup

1. **Config Setup:**
   Copy the **Auth.php**, **AuthGroups.php**, and **AuthToken.php** from **vendor/codeigniter4/shield/src/Config/** into your project's config folder and update the namespace to `Config`. You will also need to have these classes extend the original classes. See the example below. These files contain all the settings, group, and permission information for your application and will need to be modified to meet the needs of your site.

    ```php
    // new file - app/Config/Auth.php
    <?php

    declare(strict_types=1);

    namespace Config;

    // ...
    use CodeIgniter\Shield\Config\Auth as ShieldAuth;

    class Auth extends ShieldAuth
    {
        // ...
    }
    ```

2. **Helper Setup:**
   The `auth` and `setting` helpers need to be included in almost every page.
   The simplest way to do this is to add it to the **app/Config/Autoload.php** file:

    ```php
    public $helpers = ['auth', 'setting'];
    ```

3. **Routes Setup:**
   The default auth routes can be setup with a single call in **app/Config/Routes.php**:

    ```php
    service('auth')->routes($routes);
    ```

4. **Security Setup:**
   Set `Config\Security::$csrfProtection` to `'session'` for security reasons, if you use Session Authenticator.

5. **Email Setup:**
   Configure **app/Config/Email.php** to allow Shield to send emails.

    ```php
    <?php

    namespace Config;

    use CodeIgniter\Config\BaseConfig;

    class Email extends BaseConfig
    {
        public string $fromEmail  = 'your_mail@example.com';
        public string $fromName   = 'your name';
        // ...
    }
    ```

6. **Migration:**
   Run the migrations.

    !!! note

        If you want to customize table names, you must change the table names before running database migrations.
        See [Customizing Table Names](../customization/table_names.md).

    ```console
    php spark migrate --all
    ```

    #### Note: migration error

    When you run `spark migrate --all`, if you get `Class "SQLite3" not found` error:

    1. Remove sample migration files in **tests/_support/Database/Migrations/**
    2. Or install `sqlite3` php extension
