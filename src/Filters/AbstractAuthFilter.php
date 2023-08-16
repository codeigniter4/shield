<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Config\Auth;

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
            if (! url_is('login')) {
                $session = session();
                $session->setTempdata('beforeLoginUrl', current_url(), 300);
            }

            return redirect()->route('login');
        }

        if ($this->isAuthorized($arguments)) {
            return;
        }

        switch ($this->filterName) {
            case 'group':
                return redirect()->to(config(Auth::class)->afterGroupDeniedRedirect())
                    ->with('error', lang('Auth.notEnoughPrivilege'));

            case 'permission':
                return redirect()->to(config(Auth::class)->afterPermissionDeniedRedirect())
                    ->with('error', lang('Auth.notEnoughPrivilege'));
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param Response|ResponseInterface $response
     * @param array|null                 $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Nothing required
    }

    abstract protected function isAuthorized(array $arguments): bool;
}
