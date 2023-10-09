# Magic Link Login

Magic Link Login is a feature that allows users to log in if they forget their
password.

## Configuration

### Configure Magic Link Login Functionality

Magic Link Login functionality is enabled by default.
You can change it within the **app/Config/Auth.php** file.

```php
public bool $allowMagicLinkLogins = true;
```

### Magic Link Lifetime

By default, Magic Link can be used for 1 hour. This can be easily modified
in the **app/Config/Auth.php** file.

```php
public int $magicLinkLifetime = HOUR;
```

## Responding to Magic Link Logins

!!! note

    You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](../getting_started/install.md#initial-setup).

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
