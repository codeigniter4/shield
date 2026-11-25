<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\LDAP;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\LDAP;

/**
 * Exercises the application login action described in the LDAP guide.
 */
final class LoginController extends BaseController
{
    /**
     * Signs the user in with LDAP and continues any pending Shield login action.
     */
    public function loginAction(): RedirectResponse
    {
        /** @var LDAP $ldap */
        $ldap   = auth('ldap')->getAuthenticator();
        $result = $ldap->attempt($this->request->getPost(['username', 'password']));

        if (! $result->isOK()) {
            return redirect()->route('login')->with('error', $result->reason());
        }

        return $ldap->hasAction()
            ? redirect()->route('auth-action-show')->withCookies()
            : redirect()->to(config('Auth')->loginRedirect())->withCookies();
    }
}
