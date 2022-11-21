<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

use CodeIgniter\Shield\Exceptions\PermissionException;

/**
 * Permission Authorization Filter.
 */
class PermissionFilter implements FilterInterface
{
    /**
     * Ensures the user is logged in and has one or
     * more permissions as specified in the filter.
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
            return redirect()->route('login');
        }

        foreach ($arguments as $permission) {
            if (auth()->user()->can($permission)) {
                return;
            }
        }

        // If the previous_url is from this site, then
        // we can redirect back to it.
        if (strpos(previous_url(), site_url()) === 0) {
            return redirect()->back()->with('error', lang('Auth.notEnoughPrivilege'));
        }

        // Otherwise, we'll just send them to the home page.
        return redirect()->to('/')->with('error', lang('Auth.notEnoughPrivilege'));
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
}
