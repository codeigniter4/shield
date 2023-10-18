<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Group Authorization Filter.
 */
class GroupFilter extends AbstractAuthFilter
{
    /**
     * Ensures the user is logged in and a member of one or
     * more groups as specified in the filter.
     */
    protected function isAuthorized(array $arguments): bool
    {
        return auth()->user()->inGroup(...$arguments);
    }

    /**
     * If the user does not belong to the group, redirect to the configured URL with an error message.
     */
    protected function redirectToDeniedUrl(): RedirectResponse
    {
        return redirect()->to(config('Auth')->groupDeniedRedirect())
            ->with('error', lang('Auth.notEnoughPrivilege'));
    }
}
