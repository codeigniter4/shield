# Custom Authenticators

CodeIgniter Shield allows you to extend authentication by creating **Custom Authenticators**.  
This is done by implementing the `CodeIgniter\Shield\Authentication\AuthenticatorInterface` contract, which ensures full compatibility with Shield’s authentication lifecycle, including `login` and `logout` events.

Custom Authenticators enable project-specific authentication strategies such as:

- External identity providers (OAuth, SAML, OpenID Connect)  
- Hardware or device challenges (USB Security Key, FIDO2, TPM, device fingerprinting)  
- Hybrid authentication mechanisms  

## Why Custom Authenticators

While Shield provides built-in authenticators such as **Session**, **AccessTokens**, **HmacSha256**, and **JWT**, custom authenticators allow you to:

- Enforce project-specific login logic  
- Integrate new or external authentication mechanisms  
- Keep full compatibility with Shield events and lifecycle  

## Implementing a Custom Authenticator

1. Create a PHP class in your `App\Auth\Authentication` namespace.
2. Implement the `CodeIgniter\Shield\Authentication\AuthenticatorInterface`.
3. Implement the required methods:

```php
<?php

declare(strict_types=1);

namespace App\Auth\Authentication;

use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Result;
use CodeIgniter\Shield\Entities\User;

class MyCustomAuthenticator implements AuthenticatorInterface
{
    public function attempt(array $credentials): Result
    {
        // Your login logic
    }

    public function check(array $credentials): Result
    {
        // Credential verification
    }

    public function loggedIn(): bool
    {
        // Return login state
    }

    public function login(User $user): void
    {
        // Store user session or token
    }

    public function loginById($userId): void
    {
        // Optional: login using user ID
    }

    public function logout(): void
    {
        // Remove session or token
    }

    public function getUser(): ?User
    {
        // Return the currently logged-in user
    }

    public function recordActiveDate(): void
    {
        // Optional: track user activity
    }
}
```

## Registering the Authenticator

In CodeIgniter Shield, all authenticators-built-in or custom—are registered through the **app/Config/Auth.php** file. This ensures that Shield recognizes your authenticator and allows you to reference it using its alias in the *auth* helper.

Open **app/Config/Auth.php** and add your custom authenticator to the `$authenticators` array:

```php
public array $authenticators = [
    'session' => \CodeIgniter\Shield\Authentication\Session::class,
    'tokens'  => \CodeIgniter\Shield\Authentication\AccessTokens::class,
    'hmac'    => \CodeIgniter\Shield\Authentication\HmacSha256::class,
    //  Register your custom authenticator
    'custom'  => \App\Auth\Authentication\MyCustomAuthenticator::class,
];
```
The array key `custom` is the alias you will use in the `auth('custom')` helper.

## Using the Authenticator

You can now use your authenticator anywhere in your application via the `auth('custom')` helper:

```php
$credentials = [
    'email'    => $this->request->getPost('email'),
    'password' => $this->request->getPost('password')
];

$result = auth('custom')->attempt($credentials );

if ($result->isOK()) {
    $user = $result->extraInfo();
    echo "Login successful for: " . $user->email;
} else {
    echo "Login failed: " . $result->reason();
}

```

Now all standard authentication methods—such as `attempt()`, `check()`, `loggedIn()`, `login()`, `loginById()`, `logout()`, `getUser()`, and `recordActiveDate()`—are fully available.