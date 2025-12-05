# Extending the Controllers

## Provided Controllers

Shield has the following controllers that can be extended to handle
various parts of the authentication process:

-   **ActionController** handles the after-login and after-registration actions, like Two Factor Authentication and Email Verification.
-   **LoginController** handles the login process.
-   **RegisterController** handles the registration process. Overriding this class allows you to customize the User Provider, the User Entity, and the validation rules.
-   **MagicLinkController** handles the password-recovery and password-less login flow. It can deliver authentication credentials as a one-time login link or a one-time code (OTP) via email, depending on configuration. Developers may extend this controller to customize user-facing messages, providing clear context about the selected magic login method instead of only swapping view templates.

## How to Extend

It is not recommended to copy the entire controller into **app/Controllers** and change its namespace. Instead, you should create a new controller that extends
the existing controller and then only override the methods needed. This allows the other methods to stay up to date with any security
updates that might happen in the controllers.

```php
<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\LoginController as ShieldLogin;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends ShieldLogin
{
    public function logoutAction(): RedirectResponse
    {
        // new functionality
    }
}
```

After extending, don't forget to change the route. See [Customizing Routes](./route_config.md).
