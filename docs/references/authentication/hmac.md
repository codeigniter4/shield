# HMAC SHA256 Token Authenticator

The HMAC-SHA256 authenticator supports the use of revocable API keys without using OAuth. This provides
an alternative to a token that is passed in every request and instead uses a shared secret that is used to sign
the request in a secure manner. Like authorization tokens, these are commonly used to provide third-party developers
access to your API. These keys typically have a very long expiration time, often years.

These are also suitable for use with mobile applications. In this case, the user would register/sign-in
with their email/password. The application would create a new access token for them, with a recognizable
name, like John's iPhone 12, and return it to the mobile application, where it is stored and used
in all future requests.

!!! note

    For the purpose of this documentation, and to maintain a level of consistency with the Authorization Tokens,
    the term "Token" will be used to represent a set of API Keys (key and secretKey).

## Usage

In order to use HMAC Keys/Token the `Authorization` header will be set to the following in the request:

```
Authorization: HMAC-SHA256 <key>:<HMAC-HASH-of-request-body>
```

The code to do this will look something like this:

```php
header("Authorization: HMAC-SHA256 {$key}:" . hash_hmac('sha256', $requestBody, $secretKey));
```

Using the CodeIgniter CURLRequest class:

```php
<?php

$client = \Config\Services::curlrequest();

$key = 'a6c460151b4cabbe1c1d73e08915ce8e';
$secretKey = '56c85232f0e5b55c05015476cd132c8d';
$requestBody = '{"name":"John","email":"john@example.com"}';

// $hashValue = b22b0ec11ad61cd4488ab1a09c8a0317e896c22adcc5754ea4cfd0f903a0f8c2
$hashValue = hash_hmac('sha256', $requestBody, $secretKey);

$response = $client->setHeader('Authorization', "HMAC-SHA256 {$key}:{$hashValue}")
    ->setBody($requestBody)
    ->request('POST', 'https://example.com/api');
```

## HMAC Keys/API Authentication

Using HMAC keys requires that you either use/extend `CodeIgniter\Shield\Models\UserModel` or
use the `CodeIgniter\Shield\Authentication\Traits\HasHmacTokens` on your own user model. This trait
provides all the custom methods needed to implement HMAC keys in your application. The necessary
database table, `auth_identities`, is created in Shield's only migration class, which must be run
before first using any of the features of Shield.

## Generating HMAC Access Keys

Access keys/tokens are created through the `generateHmacToken()` method on the user. This takes a name to
give to the token as the first argument. The name is used to display it to the user, so they can
differentiate between multiple tokens.

```php
$token = $user->generateHmacToken('Work Laptop');
```

This creates the keys/tokens using a cryptographically secure random string. The keys operate as shared keys.
This means they are stored as-is in the database. The method returns an instance of
`CodeIgniters\Shield\Authentication\Entities\AccessToken`. The field `secret` is the 'key' the field `secret2` is
the shared 'secretKey'. Both are required to when using this authentication method.

**The plain text version of these keys should be displayed to the user immediately, so they can copy it for
their use.** It is recommended that after that only the 'key' field is displayed to a user.  If a user loses the
'secretKey', they should be required to generate a new set of keys to use.

```php
$token = $user->generateHmacToken('Work Laptop');

echo 'Key: ' . $token->secret;
echo 'SecretKey: ' . $token->secret2;
```

## Revoking HMAC Keys

HMAC keys can be revoked through the `revokeHmacToken()` method. This takes the key as the only
argument. Revoking simply deletes the record from the database.

```php
$user->revokeHmacToken($key);
```

You can revoke all HMAC Keys with the `revokeAllHmacTokens()` method.

```php
$user->revokeAllHmacTokens();
```

## Retrieving HMAC Keys

The following methods are available to help you retrieve a user's HMAC keys:

```php
// Retrieve a set of HMAC Token/Keys by key
$token = $user->getHmacToken($key);

// Retrieve an HMAC token/keys by its database ID
$token = $user->getHmacTokenById($id);

// Retrieve all HMAC tokens as an array of AccessToken instances.
$tokens = $user->hmacTokens();
```

## HMAC Keys Lifetime

HMAC Keys/Tokens will expire after a specified amount of time has passed since they have been used.
This uses the same configuration value as AccessTokens.

By default, this is set to 1 year. You can change this value by setting the `$unusedTokenLifetime`
value in the **app/Config/AuthToken.php** config file. This is in seconds so that you can use the
[time constants](https://codeigniter.com/user_guide/general/common_functions.html#time-constants)
that CodeIgniter provides.

```php
public $unusedTokenLifetime = YEAR;
```

## HMAC Keys Scopes

Each token (set of keys) can be given one or more scopes they can be used within. These can be thought of as
permissions the token grants to the user. Scopes are provided when the token is generated and
cannot be modified afterword.

```php
$token = $user->gererateHmacToken('Work Laptop', ['posts.manage', 'forums.manage']);
```

By default, a user is granted a wildcard scope which provides access to all scopes. This is the
same as:

```php
$token = $user->gererateHmacToken('Work Laptop', ['*']);
```

During authentication, the HMAC Keys the user used is stored on the user. Once authenticated, you
can use the `hmacTokenCan()` and `hmacTokenCant()` methods on the user to determine if they have access
to the specified scope.

```php
if ($user->hmacTokenCan('posts.manage')) {
    // do something....
}

if ($user->hmacTokenCant('forums.manage')) {
    // do something....
}
```
