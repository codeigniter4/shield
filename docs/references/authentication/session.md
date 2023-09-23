# Session Authenticator

The Session authenticator stores the user's authentication within the user's session, and on a secure cookie
on their device. This is the standard password-based login used in most web sites. It supports a
secure remember-me feature, and more. This can also be used to handle authentication for
single page applications (SPAs).

## Method References

### attempt()

When a user attempts to login with their email and password, you would call the `attempt()` method
on the auth class, passing in their credentials.

```php
$credentials = [
    'email'    => $this->request->getPost('email'),
    'password' => $this->request->getPost('password')
];

$loginAttempt = auth()->attempt($credentials);

if (! $loginAttempt->isOK()) {
    return redirect()->back()->with('error', $loginAttempt->reason());
}
```

Upon a successful `attempt()`, the user is logged in. The Response object returned will provide
the user that was logged in as `extraInfo()`.

```php
$result = auth()->attempt($credentials);

if ($result->isOK()) {
    $user = $result->extraInfo();
}
```

If the attempt fails a `failedLogin` event is triggered with the credentials array as
the only parameter. Whether or not they pass, a login attempt is recorded in the `auth_logins` table.

If `allowRemembering` is `true` in the `Auth` config file, you can tell the Session authenticator
to set a secure remember-me cookie.

```php
$loginAttempt = auth()->remember()->attempt($credentials);
```

### check()

If you would like to check a user's credentials without logging them in, you can use the `check()`
method.

```php
$credentials = [
    'email'    => $this->request->getPost('email'),
    'password' => $this->request->getPost('password')
];

$validCreds = auth()->check($credentials);

if (! $validCreds->isOK()) {
    return redirect()->back()->with('error', $validCreds->reason());
}
```

The Result instance returned contains the valid user as `extraInfo()`.

### loggedIn()

You can determine if a user is currently logged in with the aptly titled method, `loggedIn()`.

```php
if (auth()->loggedIn()) {
    // Do something.
}
```

### logout()

You can call the `logout()` method to log the user out of the current session. This will destroy and
regenerate the current session, purge any remember-me tokens current for this user, and trigger a
`logout` event.

```php
auth()->logout();
```

### forget()

The `forget` method will purge all remember-me tokens for the current user, making it so they
will not be remembered on the next visit to the site.

## Events and Logging

The following is a list of Events and Logging for Session Authenticator.

### Register

- Default Register
    - Post email/username/password
        - OK → event `register` and `login`
        - NG → no event
- Register with Email Activation
    1. Post email/username/password
        - OK → event `register`
        - NG → no event
    2. Post token
        - OK → event `login`
        - NG → no event

### Login

- Default Login
    - Post email/password
        - OK → event `login` / table `auth_logins`
        - NG → event `failedLogin` / table `auth_logins`
- Email2FA Login
    1. Post email/password
        - OK → no event / table `auth_logins`
        - NG → event `failedLogin` / table `auth_logins`
    2. Post token
        - OK → event `login`
        - NG → no event
- Remember-me
    - Send remember-me cookie w/o session cookie
        - OK → no event
        - NG → no event
- Magic-link
    1. Post email
        - OK → no event
        - NG → no event
    2. Send request with token
        - OK → event `login` and `magicLogin` / table `auth_logins`
        - NG → event `failedLogin` / table `auth_logins`
