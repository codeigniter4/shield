# Upgrade Guide

## Version 1.0.0-beta.6 to 1.0.0-beta.7

### The minimum CodeIgniter version

Shield requires CodeIgniter 4.3.5 or later.
Versions prior to 4.3.5 have known vulnerabilities.
See https://github.com/codeigniter4/CodeIgniter4/security/advisories

### Mandatory Config Changes

#### New Config\AuthToken

A new Config file **AuthToken.php** has been introduced. Run `php spark shield:setup`
again to install it into **app/Config/**, or install it manually.

Then change the default settings as necessary. When using Token authentication,
the default value has been changed from all accesses to be recorded in the
``token_logins`` table to only accesses that fail authentication to be recorded.

#### Config\Auth

The following items have been moved. They are no longer used and should be removed.

- `$authenticatorHeader` and `$unusedTokenLifetime` are moved to `Config\AuthToken`.

The following items have been added. Copy the properties in **src/Config/Auth.php**.

- `$usernameValidationRules` and `$emailValidationRules` are added.

## Version 1.0.0-beta.3 to 1.0.0-beta.4

### Important Password Changes

#### Password Incompatibility

Shield 1.0.0-beta.4 fixes a [vulnerability related to password storage](https://github.com/codeigniter4/shield/security/advisories/GHSA-c5vj-f36q-p9vg).
As a result, hashed passwords already stored in the database are no longer compatible
and cannot be used by default.

All hashed passwords stored in Shield v1.0.0-beta.3 or earlier are easier to
crack than expected due to the above vulnerability. Therefore, they should be
removed as soon as possible.

Existing users will no longer be able to log in with their passwords and will
need to log in with the magic link and then set their passwords again.

#### If You Want to Allow Login with Existing Passwords

If you want to use passwords saved in Shield v1.0.0-beta.3 or earlier,
you must add the following property in `app/Config/Auth.php`:

```php
    public bool $supportOldDangerousPassword = true;
```

After upgrading, with the above setting, once a user logs in with the password,
the hashed password is updated and stored in the database.

In this case, the existing hashed passwords are still easier to crack than expected.
Therefore, this setting should not be used for an extended period of time.
So you should change the setting to `false` as soon as possible, and remove old
hashed password.

> **Note**
>
> This setting is deprecated. It will be removed in v1.0.0 official release.

#### Limitations for the Default Password Handling

By default, Shield uses the hashing algorithm `PASSWORD_DEFAULT` (see `app/Config/Auth.php`),
that is, `PASSWORD_BCRYPT` at the time of writing.

Now there are two limitations when you use `PASSWORD_BCRYPT`.

1. the password will be truncated to a maximum length of 72 bytes.
2. the password will be truncated at the first NULL byte (`\0`).

If these behaviors are unacceptable, see [How to Strengthen the Password](https://github.com/codeigniter4/shield/blob/develop/docs/guides/strengthen_password.md).
