# Authentication Actions

Authentication Actions are a way to group actions that can happen after login or registration. 
Shield ships with two actions you can use, and makes it simple for you to define your own.

1. **Email-based Two Factor Authentication** (Email2FA) will send a 6-digit code to the user's 
    email address that they must confirm before they can continue.
2. **Email-based Account Activation** (EmailActivate) confirms a new user's email address by 
    sending them an email with a link they must follow in order to have their account activated. 
    
## Configuring Actions

Actions are setup in the `Auth` config file, with the `$actions` variable. 

```
public $actions = [
    'login'    => null,
    'register' => null,
];
```

To define an action to happen you will specify the class name as the value for the appropriate task:

```
public $actions = [
    'login'    => \Shield\Authentication\Actions\Email2FA::class,
    'register' => \Shield\Authentication\Actions\EmailActivate::class,
];
```

Once configured, everything should work out of the box. The routes are added with the basic `auth()->routes($routes)` 
call, but can be manually added if you choose not to use this helper method. 

```
$routes->get('auth/a/show', 'ActionController::show');
$routes->post('auth/a/handle', 'ActionController::handle');
$routes->post('auth/a/verify', 'ActionController::verify');
```

Views for all of these pages are defined in `Config\Auth->views` array.

NOTE: a session flag is set with the current action step and the user cannot continue until that
flag has been cleared. 

## Defining New Actions

While the provided email-based activation and 2FA will work for many sites, others will have different
needs, like using SMS to verify or something completely different. Actions have only one requirement: 
they must implement `Sparks\Shield\Authentication\Actions\ActionInterface`. The interface defines 
three methods:

**show()** should display the initial page the user lands on immediately after the authentication task,
like login. It will typically display instructions to the user and provide an action to take, like
clicking a button to have an email or SMS message sent. You might verify email address or phone numbers
here. 

**handle()** is the next page the user would land on and can be used to handle the action the `show()`
told the user would be happening. For example, in the `Email2FA` class, this method generates the code, 
sends the email to the user, and then displays the form the user should enter the 6 digit code into.

**verify()** is the final step in the action's journey. It verifies the information the user provided
and provides feedback. One important task is to remove the `auth_action` field from the session so 
that a user can proceed through the site like normal. In the `Email2FA` class, it verifies the code
against what is saved in the database and either sends them back to the previous form to try again
or redirects the user to the page that a `login` task would have redirected them to anyway.

All methods should return either a RedirectResponse or string of a view, like through the `view()` method.
