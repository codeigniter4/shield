# Authentication

- [Authentication](#authentication)
  - [Available Authenticators](#available-authenticators)
  - [Auth Helper](#auth-helper)
  - [Authenticator Responses](#authenticator-responses)
    - [isOK()](#isok)
    - [reason()](#reason)
    - [extraInfo()](#extrainfo)
  - [Session Authenticator](#session-authenticator)
    - [attempt()](#attempt)
    - [check()](#check)
    - [loggedIn()](#loggedin)
    - [logout()](#logout)
    - [forget()](#forget)
  - [Access Token Authenticator](#access-token-authenticator)
    - [Access Token/API Authentication](#access-tokenapi-authentication)
    - [Generating Access Tokens](#generating-access-tokens)
    - [Revoking Access Tokens](#revoking-access-tokens)
    - [Retrieving Access Tokens](#retrieving-access-tokens)
    - [Access Token Lifetime](#access-token-lifetime)
    - [Access Token Scopes](#access-token-scopes)

Authentication is the process of determining that a visitor actually belongs to your website,
and identifying them. Shield provides a flexible and secure authentication system for your
web apps and APIs.

## Available Authenticators

Shield ships with 2 authenticators that will serve several typical situations within web app development: the
Session authenticator, which uses username/email/password to authenticate against and stores it in the session,
and the Access Tokens authenticator which uses private access tokens passed in the headers.

The available authenticators are defined in `Config\Auth`:

```php
public $authenticators = [
    // alias  => classname
    'session' => Session::class,
    'tokens'  => AccessTokens::class,
];
```

The default authenticator is also defined in the configuration file, and uses the alias given above:

```php
public $defaultAuthenticator = 'session';
```

## Auth Helper

The auth functionality is designed to be used with the `auth_helper` that comes with Shield. This
helper method provides the `auth()` function which returns a convenient interface to the most frequently
used functionality within the auth libraries.

```php
// get the current user
auth()->user();

// get the current user's id
auth()->id();
// or
user_id();

// get the User Provider (UserModel by default)
auth()->getProvider();
```

> **Note**
> The `auth_helper` is autoloaded by Composer. If you want to *override* the functions,
> you need to define them in **app/Common.php**.

## Authenticator Responses

Many of the authenticator methods will return a `CodeIgniter\Shield\Result` class. This provides a consistent
way of checking the results and can have additional information returned along with it. The class
has the following methods:

### isOK()

Returns a boolean value stating whether the check was successful or not.

### reason()

Returns a message that can be displayed to the user when the check fails.

### extraInfo()

Can return a custom bit of information. These will be detailed in the method descriptions below.


## Session Authenticator

The Session authenticator stores the user's authentication within the user's session, and on a secure cookie
on their device. This is the standard password-based login used in most web sites. It supports a
secure remember me feature, and more. This can also be used to handle authentication for
single page applications (SPAs).

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



## Access Token Authenticator

The Access Token authenticator supports the use of revoke-able API tokens without using OAuth. These are commonly
used to provide third-party developers access to your API. These tokens typically have a very long
expiration time, often years.

These are also suitable for use with mobile applications. In this case, the user would register/sign-in
with their email/password. The application would create a new access token for them, with a recognizable
name, like John's iPhone 12, and return it to the mobile application, where it is stored and used
in all future requests.

### Access Token/API Authentication

Using access tokens requires that you either use/extend `CodeIgniter\Shield\Models\UserModel` or
use the `CodeIgniter\Shield\Authentication\Traits\HasAccessTokens` on your own user model. This trait
provides all of the custom methods needed to implement access tokens in your application. The necessary
database table, `auth_identities`, is created in Shield's only migration class, which must be run
before first using any of the features of Shield.

### Generating Access Tokens

Access tokens are created through the `generateAccessToken()` method on the user. This takes a name to
give to the token as the first argument. The name is used to display it to the user so they can
differentiate between multiple tokens.

```php
$token = $user->generateAccessToken('Work Laptop');
```

This creates the token using a cryptographically secure random string. The token
is hashed (sha256) before saving it to the database. The method returns an instance of
`CodeIgniters\Shield\Authentication\Entities\AccessToken`. The only time a plain text
version of the token is available is in the `AccessToken` returned immediately after creation.

**The plain text version should be displayed to the user immediately so they can copy it for
their use.** If a user loses it, they cannot see the raw version anymore, but they can generate
a new token to use.

```php
$token = $user->generateAccessToken('Work Laptop');

// Only available immediately after creation.
echo $token->raw_token;
```

### Revoking Access Tokens

Access tokens can be revoked through the `revokeAccessToken()` method. This takes the plain-text
access token as the only argument. Revoking simply deletes the record from the database.

```php
$user->revokeAccessToken($token);
```

Typically, the plain text token is retrieved from the request's headers as part of the authentication
process. If you need to revoke the token for another user as an admin, and don't have access to the
token, you would need to get the user's access tokens and delete them manually.

You can revoke all access tokens with the `revokeAllAccessTokens()` method.

```php
$user->revokeAllAccessTokens();
```

### Retrieving Access Tokens

The following methods are available to help you retrieve a user's access tokens:

```php
// Retrieve a single token by plain text token
$token = $user->getAccessToken($rawToken);

// Retrieve a single token by it's database ID
$token = $user->getAccessTokenById($id);

// Retrieve all access tokens as an array of AccessToken instances.
$tokens = $user->accessTokens();
```

### Access Token Lifetime

Tokens will expire after a specified amount of time has passed since they have been used.
By default, this is set to 1 year. You can change this value by setting the `accessTokenLifetime`
value in the `Auth` config file. This is in seconds so that you can use the
[time constants](https://codeigniter.com/user_guide/general/common_functions.html#time-constants)
that CodeIgniter provides.

```php
public $unusedTokenLifetime = YEAR;
```

### Access Token Scopes

Each token can be given one or more scopes they can be used within. These can be thought of as
permissions the token grants to the user. Scopes are provided when the token is generated and
cannot be modified afterword.

```php
$token = $user->gererateAccessToken('Work Laptop', ['posts.manage', 'forums.manage']);
```

By default a user is granted a wildcard scope which provides access to all scopes. This is the
same as:

```php
$token = $user->gererateAccessToken('Work Laptop', ['*']);
```

During authentication, the token the user used is stored on the user. Once authenticated, you
can use the `tokenCan()` and `tokenCant()` methods on the user to determine if they have access
to the specified scope.

```php
if ($user->tokenCan('posts.manage')) {
    // do something....
}

if ($user->tokenCant('forums.manage')) {
    // do something....
}
```
