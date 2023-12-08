# Upgrade Guide

## Version 1.0.0-beta.8 to 1.0.0

## Removed Deprecated Items

The [$supportOldDangerousPassword](#if-you-want-to-allow-login-with-existing-passwords)
feature for backward compatiblity has been removed. The old passwords saved in
Shield v1.0.0-beta.3 or earlier are no longer supported.

## Version 1.0.0-beta.7 to 1.0.0-beta.8

### Mandatory Config Changes

#### Helper Autoloading

Helper autoloading has been changed to be done by CodeIgniter's autoloader
instead of Composer.

So you need to update the settings. Run `php spark shield:setup` again, and the
following steps will be done.

1. Add `auth` and `setting` to the `$helpers` array in **app/Config/Autoload.php**:

    ```php
    public $helpers = ['auth', 'setting'];
    ```

2. Remove the following code in the `initController()` method in
   `**app/Controllers/BaseController.php**:

    ```php
    $this->helpers = array_merge($this->helpers, ['setting']);
    ```

#### Config\Auth

The following items have been added. Copy the properties in **src/Config/Auth.php**.

- `permission_denied` and `group_denied` are added to `Config\Auth::$redirects`.
- `permissionDeniedRedirect()` and `groupDeniedRedirect()` are added.

### Fix Custom Filter If extends `AbstractAuthFilter`

If you have written a custom filter that extends `AbstractAuthFilter`, now you need to add and implement the `redirectToDeniedUrl()` method to your custom filter.
The following example is related to the above explanation for **group** filter.

```php
/**
 * If the user does not belong to the group, redirect to the configured URL with an error message.
 */
protected function redirectToDeniedUrl(): RedirectResponse
{
    return redirect()->to(config('Auth')->groupDeniedRedirect())
        ->with('error', lang('Auth.notEnoughPrivilege'));
}
```

### Fix to HMAC Secret Key Encryption

#### Config\AuthToken

If you are using the HMAC authentication you need to update the encryption settings in **app/Config/AuthToken.php**.
You will need to update and set the encryption key in `$hmacEncryptionKeys`. This should be set using **.env** and/or
system environment variables. Instructions on how to do that can be found in the
[Setting Your Encryption Key](https://codeigniter.com/user_guide/libraries/encryption.html#setting-your-encryption-key)
section of the CodeIgniter 4 documentation and in [HMAC SHA256 Token Authenticator](./docs/references/authentication/hmac.md#hmac-secret-key-encryption).

You also may wish to adjust the default Driver `$hmacEncryptionDefaultDriver` and the default Digest
`$hmacEncryptionDefaultDigest`, these currently default to `'OpenSSL'` and `'SHA512'` respectively.

#### Encrypt Existing Keys

After updating the key in `$hmacEncryptionKeys` value, you will need to run `php spark shield:hmac encrypt` in order
to encrypt any existing HMAC tokens. This only needs to be run if you have existing unencrypted HMAC secretKeys in
stored in the database.

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
