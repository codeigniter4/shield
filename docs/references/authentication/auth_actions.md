# Authentication Actions

Authentication Actions are a way to group actions that can happen after login or registration.
Shield ships with two actions you can use, and makes it simple for you to define your own.

1. **Email-based Account Activation** (EmailActivate) confirms a new user's email address by
   sending them an email with a link they must follow in order to have their account activated.
2. **Email-based Two Factor Authentication** (Email2FA) will send a 6-digit code to the user's
    email address that they must confirm before they can continue.

## Configuring Actions

Actions are setup in the `Auth` config file, with the `$actions` variable.

```php
public array $actions = [
    'register' => null,
    'login'    => null,
];
```

To define an action to happen you will specify the class name as the value for the appropriate task:

```php
public array $actions = [
    'register' => \CodeIgniter\Shield\Authentication\Actions\EmailActivator::class,
    'login'    => \CodeIgniter\Shield\Authentication\Actions\Email2FA::class,
];
```

You must register actions in the order of the actions to be performed.
Once configured, everything should work out of the box.

The routes are added with the basic `auth()->routes($routes)`
call, but can be manually added if you choose not to use this helper method.

```php
use CodeIgniter\Shield\Controllers\ActionController;

$routes->get('auth/a/show', 'ActionController::show');
$routes->post('auth/a/handle', 'ActionController::handle');
$routes->post('auth/a/verify', 'ActionController::verify');
```

Views for all of these pages are defined in the `Auth` config file, with the `$views` array.

```php
public $views = [
    'action_email_2fa'            => '\CodeIgniter\Shield\Views\email_2fa_show',
    'action_email_2fa_verify'     => '\CodeIgniter\Shield\Views\email_2fa_verify',
    'action_email_2fa_email'      => '\CodeIgniter\Shield\Views\Email\email_2fa_email',
    'action_email_activate_show'  => '\CodeIgniter\Shield\Views\email_activate_show',
    'action_email_activate_email' => '\CodeIgniter\Shield\Views\Email\email_activate_email',
];
```

### Bot Detection

The `ActionController::verify()` method includes built-in protection against web crawlers and bots. When a bot (such as Googlebot, Bingbot, etc.) attempts to access verification links, the system will return a 404 error instead of processing the request.

This security feature prevents bots from accidentally or intentionally consuming verification tokens or codes by following links in emails during their crawling activities.

CodeIgniter automatically handles bot detection through its User Agent library. It checks the User-Agent string against the `UserAgents::robots` config defined in **app/Config/UserAgents.php** to identify known web crawlers.

## Defining New Actions

While the provided email-based activation and 2FA will work for many sites, others will have different
needs, like using SMS to verify or something completely different. Custom actions must adhere to the following requirements:

1. The class name for a "register" action must end with the suffix `Activator` (e.g., `SMSActivator`) to ensure consistency.
2. All custom actions must implement the `CodeIgniter\Shield\Authentication\Actions\ActionInterface`.

The `ActionInterface` defines three required methods that must be implemented to ensure the action integrates properly with the `ActionController`.

**show()** should display the initial page the user lands on immediately after the authentication task,
like login. It will typically display instructions to the user and provide an action to take, like
clicking a button to have an email or SMS message sent. You might verify email address or phone numbers
here.

**handle()** is the next page the user would land on and can be used to handle the action the `show()`
told the user would be happening. For example, in the `Email2FA` class, this method generates the code,
sends the email to the user, and then displays the form the user should enter the 6 digit code into.

**verify()** is the final step in the action's journey. It verifies the information the user provided
and provides feedback. In the `Email2FA` class, it verifies the code against what is saved in the
database and either sends them back to the previous form to try again or redirects the user to the
page that a `login` task would have redirected them to anyway.

All methods should return either a `Response` or a view string (e.g. using the `view()` function).

## Conditional Actions

Some applications only need an action for certain users. For example, you may
want email-based 2FA for administrators, but not for every user.

To make an action conditional, implement `ConditionalActionInterface`:

```php
<?php

namespace App\Authentication\Actions;

use CodeIgniter\Shield\Authentication\Actions\ConditionalActionInterface;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Entities\User;

final class AdminEmail2FA extends Email2FA implements ConditionalActionInterface
{
    public function appliesTo(User $user): bool
    {
        return $user->inGroup('admin', 'superadmin');
    }
}
```

Then register your conditional action in **app/Config/Auth.php**:

```php
public array $actions = [
    'register' => null,
    'login'    => \App\Authentication\Actions\AdminEmail2FA::class,
];
```

When `appliesTo()` returns `true`, Shield starts the action as usual and
discovers any stored identity for that action. When it returns `false`, Shield
does not start the action and ignores stored identities for that action while
the condition remains false. The exception is activation: if a user is already
inactive and has a stored activation identity, Shield continues to require that
activation before login can complete.

The `appliesTo()` method may be called more than once while Shield checks for
actions, so keep it deterministic, free of side effects, and fail closed when
the condition cannot be determined. It is not a replacement for authorization.

Once an action is already pending in the session, Shield continues that pending
action instead of rechecking the condition.

## Gateway Actions

Shield allows one configured action for each authentication event, such as
`login` or `register`. If your application needs to choose between multiple
ways to complete that action, register one custom action as a gateway.
Use this when users may have different verification methods enabled, but Shield
should still treat them as one login action.

A gateway action is a normal custom action. It can also be conditional, so it
only runs for users who have at least one supported method available. For
example, a login action can check whether the user has any two-factor methods
enabled:

```php
public function appliesTo(User $user): bool
{
    return $user->getIdentity('mfa_email') !== null
        || $user->getIdentity('mfa_sms') !== null;
}
```

Then register the gateway action as the login action:

```php
public array $actions = [
    'register' => null,
    'login'    => \App\Authentication\Actions\TwoFactorGateway::class,
];
```

The gateway action owns the choice between methods:

1. `show()` displays the available methods for the pending user.
2. `handle()` validates the selected method, sends the challenge, and remembers
   the selected method.
3. `verify()` verifies the challenge and completes the action.

Use one action identity type for the gateway, returned by `getType()`. For
example, the gateway might return `mfa_gateway`. Do not create separate action
identities for each method, such as `email_2fa` and `sms_2fa`, because Shield
discovers the pending action through the configured action's type.

Keep the action identity's `extra` value as the pending action message. If the
gateway needs to remember internal state, such as the selected method, store it
somewhere else, like `secret2` or an application-owned table. If the stored
value is sensitive, prefer application-owned protected storage.

This pattern is useful for application-owned flows, especially when the choices
are code-delivery methods such as email or SMS. It does not make Shield provide
built-in MFA. Your application is still responsible for enrollment, delivery,
recovery, and method-specific security rules.
