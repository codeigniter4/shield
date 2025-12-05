# Magic Login

Magic Login is an authentication feature that allows users to sign in using a one-time login link(magic link) or a one-time verification code(opt-code) sent to their email. This method provides a secure, password-less entry option, and is especially valuable for users who have forgotten their password, offering a fast and friction-free recovery experience.

## Configuration

### Configure Magic Login Functionality

Magic Login functionality is enabled by default.
You can change it within the **app/Config/Auth.php** file.

```php
public bool $allowMagicLinkLogins = true;
```

### Magic Login Mode 

Defines the format and type of the one-time credential sent to users for magic login. The system supports both password-less login via email link as well as multiple one-time verification code formats. The delivery method and code type are specified using configurable mode patterns.

#### Supported Modes

`clickable` (default) — Sends a secure, one-time login link to the user’s email that can be clicked to authenticate instantly.

`<length>-numeric` — Sends a numeric one-time code with the defined character length.

`<length>-alpha` — Sends an alphabet-only one-time code with the defined character length.

`<length>-alnum` — Sends an alphanumeric one-time code with the defined character length.

`<length>-oneof` — Sends a one-time code with the defined length, while the system automatically selects the random code type from: numeric, alpha, or alnum.

```php
public string $magicLoginMode = '8-oneof';
```

### Magic Lifetime

By default, Magic can be used for 1 hour. This can be easily modified
in the **app/Config/Auth.php** file.

```php
public int $magicLinkLifetime = HOUR;
```

!!! warning

    One-time numeric codes (such as a 6-digit `6-numeric` format) are inherently more predictable and may be easier to guess. If you plan to deliver a one-time password (OTP) instead of a magic link, we strongly recommend using the `6-oneof` credential mode, which allows the system to randomly generate numeric, alphabetic, or alphanumeric codes. This approach significantly increases unpredictability and makes OTP guessing more difficult.

    Additionally, to further mitigate brute-force and code-guessing risks, it is advisable to reduce the `$magicLinkLifetime` to a short window of only a few minutes (e.g., 120–300 seconds).


### Bot Detection

Some apps or devices may try to be "too helpful" by automatically visiting links - for example, to check if they're safe or to prepare for read-aloud features. Since this is a one-time magic link(`clickable`), such automated visits could invalidate it. To prevent this, Shield relies on the framework's `UserAgents::robots` config property (**app/Config/UserAgents.php**) to filter out requests that are likely initiated by non-human agents.

Unlike one-time login links, one-time codes (OTP) are not impacted by automated URL visits, since they require manual user input to complete authentication. Therefore, if your application delivers OTP(`6-numeric`,`6-alpha`,`6-alnum`,`6-oneof`) credentials, bot auto-visits do not introduce the same credential-invalidation risk.

## Responding to Magic Logins

!!! note

    You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](../getting_started/install.md#initial-setup).

Magic Login allows users who have forgotten their password to authenticate using a secure, one-time credential delivered via email. Depending on configuration or product requirements, this credential can be either:

1. unique, single-use login link

2. one-time opt-code for manual entry in the application

After authentication succeeds, the system behavior is up to the developer. Possible responses include redirecting the user to a dedicated password reset screen, or displaying a one-time confirmation message encouraging them to update their password from their account settings.

### Session Notification

You can detect if a user has finished the magic login by checking for a session value, `magicLogin`. If they have recently completed the flow, it will exist and have a value of `true`.

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
