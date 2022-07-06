<?php

namespace CodeIgniter\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends BaseController
{
    protected $helpers = ['auth', 'setting'];

    /**
     * Displays the form the login to the site.
     *
     * @return RedirectResponse|string
     */
    public function loginView()
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->loginRedirect());
        }

        return view(setting('Auth.views')['login']);
    }

    /**
     * Attempts to log the user in.
     */
    public function loginAction(): RedirectResponse
    {
        // Validate here first, since some things,
        // like the password, can only be validated properly here.
        $rules = $this->getValidationRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $credentials             = $this->request->getPost(setting('Auth.validFields'));
        $credentials             = array_filter($credentials);
        $credentials['password'] = $this->request->getPost('password');
        $remember                = (bool) $this->request->getPost('remember');

        // Attempt to login
        $result = auth('session')->remember($remember)->attempt($credentials);
        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        // If an action has been defined for login, start it up.
        $actionClass = setting('Auth.actions')['login'] ?? null;
        if (! empty($actionClass)) {
            return redirect()->route('auth-action-show')->withCookies();
        }

        return redirect()->to(config('Auth')->loginRedirect())->withCookies();
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return string[]
     */
    protected function getValidationRules(): array
    {
        return setting('Validation.login') ?? [
            //'username' => static::getUsernameRules(),
            'email'    => static::getEmailRules(),
            'password' => 'required',
        ];
    }

    /**
     * Returns the validation rules for username
     *
     * @return string[]
     */
    public static function getUsernameRules(): array
    {
        return [
            'required',
            'max_length[30]',
            'min_length[3]',
            'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
        ];
    }

    /**
     * Returns the validation rules for email
     *
     * @return string[]
     */
    public static function getEmailRules(): array
    {
        return [
            'required',
            'max_length[254]',
            'valid_email',
        ];
    }

    /**
     * Logs the current user out.
     */
    public function logoutAction(): RedirectResponse
    {
        auth()->logout();

        return redirect()->to(config('Auth')->logoutRedirect())->with('message', lang('Auth.successLogout'));
    }
}
