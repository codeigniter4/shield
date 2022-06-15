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
        $credentials = $this->request->getPost(setting('Auth.validFields'));
        $credentials = array_filter($credentials);

        $rules = $this->getValidationRules($credentials);

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

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
            return redirect()->to(route_to('auth-action-show'))->withCookies();
        }

        return redirect()->to(config('Auth')->loginRedirect())->withCookies();
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @param array $identifier email or username
     *
     * @return string[]
     */
    protected function getValidationRules(array $identifier): array
    {
        $rules = [
            'password' => 'required',
        ];

        if (isset($identifier['email'])) {
            $rules['email'] = 'required|max_length[254]|valid_email';
        }
        if (isset($identifier['username'])) {
            $rules['username'] = 'required|alpha_numeric_space|min_length[3]';
        }

        if (count($rules) === 1) {
            $rules['email'] = 'required|max_length[254]|valid_email';
        }

        return $rules;
    }

    /**
     * Logs the current user out.
     *
     * @return RedirectResponse
     */
    public function logoutAction()
    {
        auth()->logout();

        return redirect()->to(config('Auth')->logoutRedirect())->with('message', lang('Auth.successLogout'));
    }
}
