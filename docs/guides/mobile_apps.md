# Mobile Authentication with Access Tokens

Access Tokens can be used to authenticate mobile applications that are consuming your API. This is similar to how you would work with [third-party users](./api_tokens.md) of your API, but with small differences in how you would issue the tokens.

## Issuing the Tokens

Typically, a mobile application would issue a request from their login screen, passing in the credentials to authenticate with. Once authenticated you would return the `raw token` within the response and that would be saved on the device to use in following API calls.

Start by creating a route that would handle the request from the login screen on the mobile device. The device name can be any arbitrary string, but is typically used to identify the device the request is being made from, like "Johns iPhone 13".

```php

// Routes.php
$routes->post('auth/token', '\App\Controllers\Auth\LoginController::mobileLogin');

// LoginController.php
namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class LoginController extends BaseController
{
    public function mobileLogin()
    {
        // Validate credentials
        $rules = setting('Validation.login') ?? [
            'email' => [
                'label' => 'Auth.email',
                'rules' => config('AuthSession')->emailValidationRules,
            ],
            'password' => [
                'label' => 'Auth.password',
                'rules' => 'required',
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setJSON(['errors' => $this->validator->getErrors()])
                ->setStatusCode(422);
        }

        // Attempt to login
        $result = auth()->attempt($this->request->getPost(setting('Auth.validFields')));
        if (! $result->isOK()) {
            return $this->response
                ->setJSON(['error' => $result->reason()])
                ->setStatusCode(401);
        }

        // Generate token and return to client
        $token = auth()->user()->generateAccessToken(service('request')->getVar('device_name'));

        return $this->response
            ->setJSON(['token' => $token->raw_token]);
    }
}
```

When making all future requests to the API, the mobile client should return the raw token in the `Authorization` header as a `Bearer` token.

> **Note**
>
> By default, `$authenticatorHeader['tokens']` is set to `Authorization`. You can change the header name by setting the `$authenticatorHeader['tokens']` value in the **app/Config/Auth.php** config file.
>
> e.g. if `$authenticatorHeader['tokens']` is set to `PersonalAccessCodes` then the mobile client should return the raw token in the `PersonalAccessCodes` header as a `Bearer` token.
