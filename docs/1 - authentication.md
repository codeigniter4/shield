# Authentication

Shield provides a flexible, secure, authentication system for your web apps and API's. 

## Available Handlers

Shield ships with 3 handlers to handle several typicaly situations within web app development. 

### Session Handler

The Session handler stores the user's authentication within the user's session, and on a secure cookie
on their device. This is the standard password-based login used in most web sites. It supports a 
secure remember me feature, and more. This can also be used to handle authentication for 
single page applications (SPAs).

### Token Handler 

The Token handler supports the use of revokable API tokens without using OAuth. These are commonly 
used to provide third-party developers access to your API. These tokens typically have a very long 
expiration time, often years. 

These are also suitable for use with mobile applications. In this case, the user would register/sign-in
with their email/password. The application would create a new access token for them, with a recognizable
name, like Lonnie's iPhone 12, and return it to the mobile application, where it is stored and used
in all future requests.


## Using Tokens

Using the access tokens requires that you either use/extend `CodeIgniter\Shield\Models\UserModel` or 
use the `CodeIgniter\Shield\Authentication\Traits\HasAccessTokens` on your own user model. This trait
provides all of the custom methods needed to implement access tokens in your application. The necessary
database table, `auth_access_tokens`, is created in Shield's only migration class, which must be ran 
before first using any of the features of Shield.

### Generating Tokens

Access tokens are created through the `generateAccessToken()` method on the user. This takes a name to 
give to the token as the first argument. The name is used to display it to the user so they can 
differentiate between multiple tokens. 

```
$token = $user->generateAccessToken('Work Laptop');
```  

This creates the token using a cryptographically secure random string. The token
is hashed (sha256) before saving it to the database. The method returns an instance of 
`CodeIgniters\Shield\Authentication\Entities\AccessToken`. The only time a plain text
version of the token is available is in the `AccessToken` returned immediately after creation.
The plain text version should be displayed to the user immediately so they can copy it for 
their use. If a user loses it, they cannot see the raw version anymore, but they can generate 
a new token to use.

```
$token = $user->generateAccessToken('Work Laptop');

// Only available immediately after creation.
echo $token->raw_token;
```  

### Revoking Access Tokens

Access tokens can be revoked through the `revokeAccessToken()` method. This takes the plain-text
access token as the only argument. Revoking simply deletes the record from the database.

```
$user->revokeAccessToken($token);
```

Typically, the plain text token is retrieved from the request's headers as part of the authentication 
process. If you need to revoke the token for another user as an admin, and don't have access to the
token, you would need to get the user's access tokens and delete them manually. 

You can revoke all access tokens with the `revokeAllAccessTokens()` method.

```
$user->revokeAllAccessTokens($token);
```

### Retrieving Access Tokens

The following methods are available to help you retrieve a user's access tokens: 

```
// Retrieve a single token by plain text token
$token = $user->getAccessToken($rawToken);

// Retrieve a single token by it's database ID
$token = $user->getAccessTokenById($id);

// Retrieve all access tokens as an array of AccessToken instances.
$tokens = $user->accessTokens();
```

### Token Scopes

Each token can be given one or more scopes they can be used within. These can be thought of as 
permissions the token grants to the user. Scopes are provided when the token is generated and
cannot be modified afterword.

```
$token = $user->gererateAccessToken('Work Laptop', ['posts:manage', 'forums:manage']);
```

By default a user is granted a wildcard scope which provides access to all scopes. This is the
same as:

```
$token = $user->gererateAccessToken('Work Laptop', ['*']);
``` 

During authentication, the token the user used is stored on the user. Once authenticated, you 
can use the `tokenCan()` and `tokenCant()` methods on the user to determine if they have access
to the specified scope.

```
if ($user->tokenCan('posts:manage')) 
{
    // do something....
}

if ($user->tokenCant('forums:manage')) 
{
    // do something....
}
```

