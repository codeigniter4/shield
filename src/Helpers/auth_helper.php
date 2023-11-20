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

use CodeIgniter\Shield\Auth;

if (! function_exists('auth')) {
    /**
     * Provides convenient access to the main Auth class
     * for CodeIgniter Shield.
     *
     * @param string|null $alias Authenticator alias
     */
    function auth(?string $alias = null): Auth
    {
        /** @var Auth $auth */
        $auth = service('auth');

        return $auth->setAuthenticator($alias);
    }
}

if (! function_exists('user_id')) {
    /**
     * Returns the ID for the current logged-in user.
     *
     * @return int|string|null
     */
    function user_id()
    {
        /** @var Auth $auth */
        $auth = service('auth');

        return $auth->id();
    }
}
