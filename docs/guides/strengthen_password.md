# How to Strengthen the Password

Shield allows you to customize password-related settings to make your passwords more secure.

## Minimum Password Length

The most important factor when it comes to passwords is the number of characters in the password.
You can check password strength with [Password Strength Testing Tool](https://bitwarden.com/password-strength/).
Short passwords may be cracked in less than one day.

In Shield, you can set the users' minimum password length. The setting is
`$minimumPasswordLength` in `app/Config/Auth.php`. The default value is 8 characters.
It is the recommended minimum value by NIST. However, some organizations recommend
12 to 14 characters.

The longer the password, the stronger it is. Consider increasing the value.

> **Note**
>
> This checking works when you validate passwords with the `strong_password`
> validation rule.
>
> If you disable `CompositionValidator` (enabled by default) in `$passwordValidators`,
> this checking will not work.

## Password Hashing Algorithm

You can change the password hashing algorithm by `$hashAlgorithm` in `app/Config/Auth.php`.
The default value is `PASSWORD_DEFAULT` that is `PASSWORD_BCRYPT` at the time of writing.

`PASSWORD_BCRYPT` means to create new password hashes using the bcrypt algorithm.

You can use `PASSWORD_ARGON2ID` if your PHP has been compiled with Argon2 support.

### PASSWORD_BCRYPT

`PASSWORD_BCRYPT` has one configuration `$hashCost`. The bigger the cost, hashed passwords will be the stronger.

You can find your appropriate cost with the following code:

```php
<?php
/**
 * This code will benchmark your server to determine how high of a cost you can
 * afford. You want to set the highest cost that you can without slowing down
 * you server too much. 8-10 is a good baseline, and more is good if your servers
 * are fast enough. The code below aims for ≤ 50 milliseconds stretching time,
 * which is a good baseline for systems handling interactive logins.
 *
 * From: https://www.php.net/manual/en/function.password-hash.php#refsect1-function.password-hash-examples
 */
$timeTarget = 0.05; // 50 milliseconds

$cost = 8;
do {
    $cost++;
    $start = microtime(true);
    password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
    $end = microtime(true);
} while (($end - $start) < $timeTarget);

echo "Appropriate Cost Found: " . $cost;
```

#### Limitations

There are two limitations when you use `PASSWORD_BCRYPT`:

1. the password will be truncated to a maximum length of 72 bytes.
2. the password will be truncated at the first NULL byte (`\0`).

##### 72 byte issue

If a user submits a password longer than 72 bytes, the validation error will occur.
If this behavior is unacceptable, consider:

1. change the hashing algorithm to `PASSWORD_ARGON2ID`. It does not have such a limitation.

##### NULL byte issue

This is because `PASSWORD_BCRYPT` is not binary-safe. Normal users cannot
send NULL bytes in a password string, so this is not a problem in most cases.

But if this behavior is unacceptable, consider:

1. adding a validation rule to prohibit NULL bytes or control codes.
2. or change the hashing algorithm to `PASSWORD_ARGON2ID`. It is binary-safe.

### PASSWORD_ARGON2ID

`PASSWORD_ARGON2ID` has three configuration `$hashMemoryCost`, `$hashTimeCost`,
and `$hashThreads`.

If you use `PASSWORD_ARGON2ID`, you should use PHP's constants:

```php
public int $hashMemoryCost = PASSWORD_ARGON2_DEFAULT_MEMORY_COST;

public int $hashTimeCost = PASSWORD_ARGON2_DEFAULT_TIME_COST;
public int $hashThreads  = PASSWORD_ARGON2_DEFAULT_THREADS;
```

## Maximum Password Length

By default, Shield has the validation rules for maximum password length.

- 72 bytes for PASSWORD_BCRYPT
- 255 characters for others

You can customize the validation rule. See [Customizing Shield](../customization.md).

## $supportOldDangerousPassword

In `app/Config/Auth.php` there is `$supportOldDangerousPassword`, which is a
setting for using passwords stored in older versions of Shield that were [vulnerable](https://github.com/codeigniter4/shield/security/advisories/GHSA-c5vj-f36q-p9vg).

This setting is deprecated.  If you have this setting set to `true`, you should change
it to `false` as soon as possible, and remove old hashed password in your database.

> **Note**
>
> This setting will be removed in v1.0.0 official release.
