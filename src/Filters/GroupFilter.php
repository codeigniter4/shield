<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

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
}
