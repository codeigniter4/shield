# Authentication

Authentication is the process of determining that a visitor actually belongs to your website,
and identifying them. Shield provides a flexible and secure authentication system for your
web apps and APIs.

## Available Authenticators

Shield ships with 4 authenticators that will serve several typical situations within web app development.
You can see the [Authenticator List](../../getting_started/authenticators.md).

The available authenticators are defined in `Config\Auth`:

```php
public array $authenticators = [
    // alias  => classname
    'session' => Session::class,
    'tokens'  => AccessTokens::class,
    'hmac'    => HmacSha256::class,
    // 'jwt'  => JWT::class,
];
```

The default authenticator is also defined in the configuration file, and uses the alias given above:

```php
public string $defaultAuthenticator = 'session';
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

!!! note

    The `auth_helper` is autoloaded by CodeIgniter's autoloader if you follow the
    installation instruction. If you want to *override* the functions, create
    **app/Helpers/auth_helper.php**.

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
