<?php

namespace CodeIgniter\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;

class LoginController extends BaseController
{
    protected $helpers = ['auth', 'setting'];

    /**
     * Displays the form the login to the site.
     */
    public function loginView(): string
    {
        return view(setting('Auth.views')['login']);
    }

    /**
     * Attempts to log the user in.
     */
    public function loginAction(): RedirectResponse
    {
        /** @var IncomingRequest $request */
        $request = service('request');

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

            return redirect()->to(route_to('auth-action-show'));
        }

        return redirect()->to(config('Auth')->loginRedirect());
    }

    /**
     * Logs the current user out.
     */
    public function logoutAction(): RedirectResponse
    {
        $user = auth()->user();

        auth()->logout();

        return redirect()->to(config('Auth')->logoutRedirect());
    }
}
