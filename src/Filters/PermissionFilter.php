<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Permission Authorization Filter.
 */
class PermissionFilter extends AbstractAuthFilter
{
    /**
     * Ensures the user is logged in and has one or more
     * of the permissions as specified in the filter.
     */
    protected function isAuthorized(array $arguments): bool
    {
        foreach ($arguments as $permission) {
            if (auth()->user()->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If there is no necessary access to the permission,
     * it will redirect the user to the set URL with an error message.
     */
    protected function redirectToDeniedUrl(): RedirectResponse
    {
        return redirect()->to(config('Auth')->permissionDeniedRedirect())
            ->with('error', lang('Auth.notEnoughPrivilege'));
    }
}
