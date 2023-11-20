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
     * If the user does not have the permission, redirect to the configured URL with an error message.
     */
    protected function redirectToDeniedUrl(): RedirectResponse
    {
        return redirect()->to(config('Auth')->permissionDeniedRedirect())
            ->with('error', lang('Auth.notEnoughPrivilege'));
    }
}
