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

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Group Authorization Filter.
 */
abstract class AbstractAuthFilter implements FilterInterface
{
    /**
     * Ensures the user is logged in and a member of one or
     * more groups as specified in the filter.
     *
     * @param array|null $arguments
     *
     * @return RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            return;
        }

        if (! auth()->loggedIn()) {
            // Set the entrance url to redirect a user after successful login
            if (uri_string() !== route_to('login')) {
                $session = session();
                $session->setTempdata('beforeLoginUrl', current_url(), 300);
            }

            return redirect()->route('login');
        }

        if ($this->isAuthorized($arguments)) {
            return;
        }

        return $this->redirectToDeniedUrl();
    }

    /**
     * We don't have anything to do here.
     *
     * @param array|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Nothing required
    }

    /**
     * Ensures the user is logged in and has one or more
     * of the permissions as specified in the filter.
     */
    abstract protected function isAuthorized(array $arguments): bool;

    /**
     * Returns redirect response when the user does not have access authorizations.
     */
    abstract protected function redirectToDeniedUrl(): RedirectResponse;
}
