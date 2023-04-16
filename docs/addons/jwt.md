# JWT Authentication

To use JWT Authentication, you need additional setup and configuration.

## Setup

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

## Configuration

Configure **app/Config/AuthJWT.php** for your needs.

### Set the Default Claims

Set the payload items by default to the property `$defaultClaims`.

E.g.:
```php
    public array $defaultClaims = [
        'iss' => 'https://codeigniter.com/',
    ];
```

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

## Generating JWTs

### JWT to a Specific User

JWTs are created through the `JWTGenerator::generateAccessToken()` method.
This takes a User object to give to the token as the first argument.
It can also take optional additional claims array, time to live in seconds,
a key group (an array key) in the `Config\AuthJWT::$keys`, and additional header
array:

```php
public function generateAccessToken(
    User $user,
    array $claims = [],
    ?int $ttl = null,
    $key = 'default',
    ?array $headers = null
): string
```

The following code generates a JWT to the user.

```php
use CodeIgniter\Shield\Authentication\TokenGenerator\JWTGenerator;

$generator = new JWTGenerator();

$user  = auth()->user();
$claims = [
    'email' => $user->email,
];
$token = $generator->generateAccessToken($user, $claims);
```

It sets the `Config\AuthJWT::$defaultClaims` to the token, and adds
the `'email'` claim and the user ID in the `"sub"` (subject) claim.
It also sets `"iat"` (Issued At) and `"exp"` (Expiration Time) claims automatically
if you don't specify.

### Arbitrary JWT

You can generate arbitrary JWT with the ``JWTGenerator::generate()`` method.

It takes a JWT claims array, and can take time to live in seconds, a key group
(an array key) in the `Config\AuthJWT::$keys`, and additional header array:

```php
public function generate(
    array $claims,
    ?int $ttl = null,
    $key = 'default',
    ?array $headers = null
): string
```

The following code generates a JWT.

```php
use CodeIgniter\Shield\Authentication\TokenGenerator\JWTGenerator;

$generator   = new JWTGenerator();

$payload = [
    'user_id' => '1',
    'email'   => 'admin@example.jp',
];
$token = $generator->generate($payload, DAY);
```

It uses the `secret` and `alg` in the `Config\AuthJWT::$keys['default']`.

It sets the `Config\AuthJWT::$defaultClaims` to the token, and sets
`"iat"` (Issued At) and `"exp"` (Expiration Time) claims automatically even if
you don't pass them.
