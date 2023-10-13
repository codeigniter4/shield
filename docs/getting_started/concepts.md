# Shield Concepts

This document covers some of the base concepts used throughout the library.

## Repository State

Shield is designed so that the initial setup of your application can all happen in code with nothing required to be
saved in the database. This means you do not have to create large seeder files that need to run within each environment.

Instead, it can be placed under version control, though the Settings library allows those settings to be easily stored
in the database if you create an interface for the user to update those settings.

## Settings

In place of the CodeIgniter `config()` helper, Shield uses the official [Settings](https://github.com/codeigniter4/settings)
library. This provides a way to save any Config class values to the database if you want to modify them, but falls back
on the standard Config class if nothing is found in the database.

## User Providers

Shield has a model to handle user persistence. Shield calls this the "User Provider" class.
A default model is provided for you by the `CodeIgniter\Shield\Models\UserModel` class.

You can use your own model to customize user attributes. See [Customizing User Provider](../customization/user_provider.md) for details.

## User Identities

User accounts are stored separately from the information needed to identify that user. These identifying pieces of data are
called User Identities. By default, the library has two types of identities: one for standard email/password information,
and one for access tokens.

Keeping these identities loosely coupled from the user account itself facilitates integrations with third-party sign-in systems, JWT systems, and more - all on a single user.

While this has the potential to make the system more complex, the `email` and `password` fields are automatically
looked up for you when attempting to access them from the User entity. Caution should be used to craft queries that will pull
in the `email` field when you need to display it to the user, as you could easily run into some n+1 slow queries otherwise.

When you `save($user)` a `User` instance in the `UserModel`, the email/password identity will automatically be updated.
If no email/password identity exists, you must pass both the email and the password to the User instance prior to calling `save()`.

## Password Validators

When registering a user account, the user's password must be validated to ensure it matches the security requirements of
your application. Shield uses a pipeline of Validators to handle the validation. This allows you turn on or off any validation
systems that are appropriate for your application. The following Validators are available:

- **CompositionValidator** validates the makeup of the password itself. This used to include things
    like ensuring it contained a symbol, a number, etc. According to the current
    [NIST recommendations](https://pages.nist.gov/800-63-3/sp800-63b.html) this only enforces a
    minimum length on the password. You can define the minimum length in
    `Config\Auth::$minimumPasswordLength` This is enabled by default. The default minimum
    value is `8`.
- **NothingPersonalValidator** will compare the password against any fields that have been specified
    in `Config\Auth::$personalFields`, like first or last names, etc. Additionally, it compares it
    against a few simple variations of the username. If the given password too closely matches
    any of the personal information, it will be rejected. The similarity value is defined in
     `Config\Auth::$maxSimilarity`. The default value is 50, but see the docblock in the config
     file for more details. This is enabled by default.
- **DictionaryValidator** will compare the password against a provided file with about 600,000
    frequently used passwords that have been seen in various data dumps over the years. If the
    chosen password matches any found in the file, it will be rejected. This is enabled by default.
- **PwnedValidator** is like the `DictionaryValidator`. Instead of comparing to a local file, it
    uses a third-party site, [Have I Been Pwned](https://haveibeenpwned.com/Passwords) to check
    against a list of over 630 million leaked passwords from many data dumps across the web.
    The search is done securely, and provides more information than the simple dictionary version.
    However, this does require an API call to a third-party which not every application will
    find acceptable. You should use either this validator or the `DictionaryValidator`, not both.
    This is disabled by default.

You can choose which validators are used in `Config\Auth::$passwordValidators`:

```php
public array $passwordValidators = [
    CompositionValidator::class,
    NothingPersonalValidator::class,
    DictionaryValidator::class,
    // PwnedValidator::class,
];
```

You use `strong_password` rule for password validation explained above.

!!! note
 
    The `strong_password` rule only supports use cases to check the user's own password.
    It fetches the authenticated user's data for **NothingPersonalValidator**
    if the visitor is authenticated.
    If you want to have use cases that set and check another user's password,
    you can't use `strong_password`. You need to use `service('passwords')` directly
    to check the password.
    But remember, it is not good practice to set passwords for other users.
    This is because the password should be known only by that user.
