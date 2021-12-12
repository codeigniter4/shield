# Vision

At its core, Shield provides a set of interfaces for authentication and authorization, and the classes required
to make auth simple. 

All common authentication/authorization tasks should be immediately available through a single helper method: `auth()`.
Note that Shield does not enforce specific authorization methods, but provides a Policy-based authorization system
for use out of the box. Example functions would be: 

```php
#
# AUTHENTICATION
#

# check a user's authorization (logs them in)
auth()->authorize($credentials)->remember(true);

# check a user's authorization without logging in
auth()->check($credentials);

# check if the user is logged in
auth()->loggedIn();

# log a user in
auth()->login($user/$id)

# log a user out
auth()->logout()

# forget a user's remember-me settings
auth()->forget()

# get the current user
auth()->user()

# get the current user from the api handler
auth('api')->user()

# get the current user's id only.
auth()->id()

# register the basic routes with the system
# used in the Routes file.
auth()->routes($routes);

# restrict the routes it generates
auth()->routes($routes, [
    'only' => ['login', 'logout'],
    'except' => ['forgot_password']
]);

#
# AUTHORIZATION
# 

# check if a user has permission to do 'action' on $entity or $model
auth()->authorize($entity/$model, 'action')
```

## Configuration

Configuration is handled within the `Config/Auth.php` class, which would look something like this:

```php
class Auth extends BaseConfig
{
    public $authenticators = [
        'session' => \Sparks\Shield\Authenticators\Session',
        'api' => \Sparks\Shield\Authenticators\ApiTokens',
    ];

    public $authorizers = [
        '\Sparks\Shield\Authorizors\Policy',
    ];

    //
    // Configs for the authenticators and authorizors
    //
    public $sessionConfig = [
        'field' => 'logged_in',
        'allowGuests' => false,
    ];

    public $formConfig = [
        'fields' => ['email', 'password'],
        'loginRoute' => 'login',    // named login  - or
        'loginUrl' => '/login',     // URL to login route 
    ];

    public $policyConfig = [
        
    ];
}
```

## Authentication

