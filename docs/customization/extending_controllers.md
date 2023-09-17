# Extending the Controllers

Shield has the following controllers that can be extended to handle
various parts of the authentication process:

-   **ActionController** handles the after-login and after-registration actions, like Two Factor Authentication and Email Verification.
-   **LoginController** handles the login process.
-   **RegisterController** handles the registration process. Overriding this class allows you to customize the User Provider, the User Entity, and the validation rules.
-   **MagicLinkController** handles the "lost password" process that allows a user to login with a link sent to their email. This allows you to
    override the message that is displayed to a user to describe what is happening, if you'd like to provide more information than simply swapping out the view used.

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
