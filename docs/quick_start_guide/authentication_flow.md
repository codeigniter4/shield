# Authentication Flow

Learning any new authentication system can be difficult, especially as they get more flexible and sophisticated. This guide is intended to provide short examples for common actions you'll take when working with Shield. It is not intended to be the exhaustive documentation for each section. That's better handled through the area-specific doc files.

> **Note**
> The examples assume that you have run the setup script and that you have copies of the `Auth` and `AuthGroups` config files in your application's **app/Config** folder.

## Configure Config\Auth

### Configure Redirect URLs

If you need everyone to redirect to a single URL after login/logout/register actions, you can modify the `Config\Auth::$redirects` array in **app/Config/Auth.php** to specify the url to redirect to.

By default, a successful login or register attempt will all redirect to `/`, while a logout action
will redirect to a [named route](https://codeigniter.com/user_guide/incoming/routing.html#using-named-routes "See routing docs") `login` or a *URI path* `/login`. You can change the default URLs used within the **app/Config/Auth.php** config file:

```php
public array $redirects = [
    'register' => '/',
    'login'    => '/',
    'logout'   => 'login',
];
```

> **Note**
> This redirect happens after the specified action is complete. In the case of register or login, it might not happen immediately. For example, if you have any Auth Actions specified, they will be redirected when those actions are completed successfully. If no Auth Actions are specified, they will be redirected immediately after registration or login.

### Configure Remember-me Functionality

Remember-me functionality is enabled by default for the `Session` authenticator. While this is handled in a secure manner, some sites may want it disabled. You might also want to change how long it remembers a user and doesn't require additional login.

```php
public array $sessionConfig = [
    'field'              => 'user',
    'allowRemembering'   => true,
    'rememberCookieName' => 'remember',
    'rememberLength'     => 30 * DAY,
];
```

### Change Access Token Lifetime

By default, Access Tokens can be used for 1 year since the last use. This can be easily modified in the `Auth` config file.

```php
public int $unusedTokenLifetime = YEAR;
```

### Enable Account Activation via Email

> **Note**
> You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](../install.md#initial-setup).

By default, once a user registers they have an active account that can be used. You can enable Shield's built-in, email-based activation flow within the `Auth` config file.

```php
public array $actions = [
    'register' => \CodeIgniter\Shield\Authentication\Actions\EmailActivator::class,
    'login'    => null,
];
```

### Enable Two-Factor Authentication

> **Note**
> You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](../install.md#initial-setup).

Turned off by default, Shield's Email-based 2FA can be enabled by specifying the class to use in the `Auth` config file.

```php
public array $actions = [
    'register' => null,
    'login'    => \CodeIgniter\Shield\Authentication\Actions\Email2FA::class,
];
```

## Responding to Magic Link Logins

> **Note**
> You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](../install.md#initial-setup).

Magic Link logins allow a user that has forgotten their password to have an email sent with a unique, one-time login link. Once they've logged in you can decide how to respond. In some cases, you might want to redirect them to a special page where they must choose a new password. In other cases, you might simply want to display a one-time message prompting them to go to their account page and choose a new password.

### Session Notification

You can detect if a user has finished the magic link login by checking for a session value, `magicLogin`. If they have recently completed the flow, it will exist and have a value of `true`.

```php
if (session('magicLogin')) {
    return redirect()->route('set_password');
}
```

This value sticks around in the session for 5 minutes. Once you no longer need to take any actions, you might want to delete the value from the session.

```php
session()->removeTempdata('magicLogin');
```

### Event

At the same time the above session variable is set, a `magicLogin` [event](https://codeigniter.com/user_guide/extending/events.html) is fired off that you may subscribe to. Note that no data is passed to the event as you can easily grab the current user from the `user()` helper or the `auth()->user()` method.

```php
Events::on('magicLogin', static function () {
    // ...
});
```
