<?php

namespace CodeIgniter\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;

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
     *
     * @return RedirectResponse
     */
    public function loginAction(): RedirectResponse
    {
        // Validate here first, since some things,
        // like the password, can only be validated properly here.
        $rules = $this->getValidationRules();

        /** @var Validation $validation */
        $validation = service('validation');

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
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
            return redirect()->to(route_to('auth-action-show'))->withCookies();
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
        $rules = [
            'email'    => 'required',
            'password' => 'required',
        ];

        if (setting('Auth.validFields') === ['email']) {
            $rules['email'] .= '|valid_email';
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
