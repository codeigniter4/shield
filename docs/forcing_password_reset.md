# Forcing Password Reset

Depending on the scope of your application, there may be times when you'll decide that it is absolutely necessary to force user(s) to reset their password. This practice is common when you find out that users of your application do not use strong passwords OR there is a reasonable suspicion that their passwords have been compromised. This guide provides you with ways to achieve this.

- [Forcing Password Reset](#forcing-password-reset)
  - [Available Methods](#available-methods)
    - [Check if a User Requires Password Reset](#check-if-a-user-requires-password-reset)
    - [Force Password Reset On a User](#force-password-reset-on-a-user)
    - [Remove Force Password Reset Flag On a User](#remove-force-password-reset-flag-on-a-user)
    - [Force Password Reset On Multiple Users](#force-password-reset-on-multiple-users)
    - [Force Password Reset On All Users](#force-password-reset-on-all-users)

## Available Methods

Shield provides a way to enforce password resets throughout your application. The `Resettable` trait on the `User` entity and the `UserIdentityModel` provides the following methods to do so.

### Check if a User Requires Password Reset

When you need to check if a user requires password reset, you can do so using the `requiresPasswordReset()` method on the `User` entity. Returns boolean `true`/`false`.

```php
if ($user->requiresPasswordReset()) {
    //...
}
```

### Force Password Reset On a User

To force password reset on a user, you can do so using the `forcePasswordReset()` method on the `User` entity.

```php
$user->forcePasswordReset();
```

### Remove Force Password Reset Flag On a User

Undoing or removing the force password reset flag on a user can be done using the `undoForcePasswordReset()` method on the `User` entity.

```php
$user->undoForcePasswordReset();
```

### Force Password Reset On Multiple Users

If you see the need to force password reset for more than one user, the `forceMultiplePasswordReset()` method of the `UserIdentityModel` allows you to do this easily. It accepts an `Array` of user IDs.

```php
use CodeIgniter\Shield\Models\UserIdentityModel;

// ...
$identities = new UserIdentityModel();
$identities->forceMultiplePasswordReset([1,2,3,4]);
```

### Force Password Reset On All Users

If you suspect a security breach or compromise in the passwords of your users, you can easily force password reset on all the users of your application using the `forceGlobalPasswordReset()` method of the `UserIdentityModel`.

```php
use CodeIgniter\Shield\Models\UserIdentityModel;

// ...
$identities = new UserIdentityModel();
$identities->forceGlobalPasswordReset();
```
