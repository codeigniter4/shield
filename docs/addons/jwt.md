# JWT Authentication

> **Note**
> Shield now supports only JWS (Singed JWT). JWE (Encrypted JWT) is not supported.

## What is JWT?

JWT or JSON Web Token is a compact and self-contained way of securely transmitting
information between parties as a JSON object. It is commonly used for authentication
and authorization purposes in web applications.

For example, when a user logs in to a web application, the server generates a JWT
token and sends it to the client. The client then includes this token in the header
of subsequent requests to the server. The server verifies the authenticity of the
token and grants access to protected resources accordingly.

If you are not familiar with JWT, we recommend that you check out
[Introduction to JSON Web Tokens](https://jwt.io/introduction) before continuing.

## Setup

To use JWT Authentication, you need additional setup and configuration.

### Manual Setup

1. Install "firebase/php-jwt" via Composer.

    ```console
    composer require firebase/php-jwt:^6.4
    ```

2. Copy the **AuthJWT.php** from **vendor/codeigniter4/shield/src/Config/** into your project's config folder and update the namespace to `Config`. You will also need to have these classes extend the original classes. See the example below.

    ```php
    // new file - app/Config/AuthJWT.php
    <?php

    declare(strict_types=1);

    namespace Config;

    use CodeIgniter\Shield\Config\AuthJWT as ShieldAuthJWT;

    /**
     * JWT Authenticator Configuration
     */
    class AuthJWT extends ShieldAuthJWT
    {
        // ...
    }
    ```

3. If your **app/Config/Auth.php** is not up-to-date, you also need to update it. Check **vendor/codeigniter4/shield/src/Config/Auth.php** and apply the differences.

## Configuration

Configure **app/Config/AuthJWT.php** for your needs.

### Set the Default Claims

> **Note**
> A payload contains the actual data being transmitted, such as user ID, role,
> or expiration time. Items in a payload is called *claims*.

Set the default payload items to the property `$defaultClaims`.

E.g.:
```php
    public array $defaultClaims = [
        'iss' => 'https://codeigniter.com/',
    ];
```

The default claims will be included in all tokens issued by Shield.

### Set Secret Key

Set your secret key in the `$keys` property, or set it in your `.env` file.

E.g.:
```dotenv
authjwt.keys.default.0.secret = 8XBFsF6HThIa7OV/bSynahEch+WbKrGcuiJVYPiwqPE=
```

It needs more than 256 bits random string. You can get a secure random string
with the following command:

```console
php -r 'echo base64_encode(random_bytes(32));'
```

> **Note**
> The secret key is used for signing and validating tokens.

## Generating Signed JWTs

### JWT to a Specific User

JWTs are created through the `JWTManager::generateToken()` method.
This takes a User object to give to the token as the first argument.
It can also take optional additional claims array, time to live in seconds,
a key group (an array key) in the `Config\AuthJWT::$keys`, and additional header
array:

```php
public function generateToken(
    User $user,
    array $claims = [],
    ?int $ttl = null,
    $keyset = 'default',
    ?array $headers = null
): string
```

The following code generates a JWT to the user.

```php
use CodeIgniter\Shield\Authentication\JWTManager;

/** @var JWTManager $manager */
$manager = service('jwtmanager');

$user  = auth()->user();
$claims = [
    'email' => $user->email,
];
$jwt = $manager->generateToken($user, $claims);
```

It sets the `Config\AuthJWT::$defaultClaims` to the token, and adds
the `'email'` claim and the user ID in the `"sub"` (subject) claim.
It also sets `"iat"` (Issued At) and `"exp"` (Expiration Time) claims automatically
if you don't specify.

### Arbitrary JWT

You can generate arbitrary JWT with the ``JWTManager::issue()`` method.

It takes a JWT claims array, and can take time to live in seconds, a key group
(an array key) in the `Config\AuthJWT::$keys`, and additional header array:

```php
public function issue(
    array $claims,
    ?int $ttl = null,
    $keyset = 'default',
    ?array $headers = null
): string
```

The following code generates a JWT.

```php
use CodeIgniter\Shield\Authentication\JWTManager;

/** @var JWTManager $manager */
$manager = service('jwtmanager');

$payload = [
    'user_id' => '1',
    'email'   => 'admin@example.jp',
];
$jwt = $manager->issue($payload, DAY);
```

It uses the `secret` and `alg` in the `Config\AuthJWT::$keys['default']`.

It sets the `Config\AuthJWT::$defaultClaims` to the token, and sets
`"iat"` (Issued At) and `"exp"` (Expiration Time) claims automatically even if
you don't pass them.
