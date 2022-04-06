<?php

namespace Sparks\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Events\Events;

class LoginController extends BaseController
{
    protected $helpers = ['setting'];

    /**
     * Displays the form the login to the site.
     */
    public function loginView()
    {
        echo view(setting('Auth.views')['login']);
    }

    /**
     * Attempts to log the user in.
     */
    public function loginAction()
    {
        $request                 = service('request');
        $credentials             = $request->getPost(setting('Auth.validFields'));
        $credentials             = array_filter($credentials);
        $credentials['password'] = $request->getPost('password');
        $remember                = (bool) $request->getPost('remember');

        // Attempt to login
        $result = auth('session')->remember($remember)->attempt($credentials);
        if (! $result->isOK()) {
            unset($credentials['password'], $credentials['password_confirm']);
            Events::trigger('failedLogin', $credentials);

            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        $user = $result->extraInfo();

        Events::trigger('didLogin', $user);

        // If an action has been defined for login, start it up.
        $actionClass = setting('Auth.actions')['login'] ?? null;
        if (! empty($actionClass)) {
            session()->set('auth_action', $actionClass);

            return redirect()->to('auth/a/show');
        }

        return redirect()->to($this->getLoginRedirect($user));
    }

    /**
     * Logs the current user out.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logoutAction()
    {
        $user = auth()->user();

        auth()->logout();

        return redirect()->to($this->getLogoutRedirect($user));
    }

    /**
     * Returns the URL that a user should be redirected
     * to after a successful login.
     *
     * @param mixed $user
     *
     * @return string
     */
    public function getLoginRedirect($user)
    {
        $url = setting('Auth.redirects')['login'];

        return strpos($url, 'http') === 0
            ? $url
            : rtrim(site_url($url), '/ ');
    }

    /**
     * Returns the URL that a user should be redirected
     * to after they are logged out.
     *
     * @param mixed $user
     *
     * @return string
     */
    protected function getLogoutRedirect($user)
    {
        $url = setting('Auth.redirects')['logout'];

        return strpos($url, 'http') === 0
            ? $url
            : rtrim(site_url($url), '/ ');
    }
}
