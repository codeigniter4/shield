<?php

use CodeIgniter\Shield\Auth;

if (! function_exists('auth')) {
    /**
     * Provides convenient access to the main Auth class
     * for CodeIgniter Shield.
     */
    function auth(?string $authenticatorAlias = null)
    {
        /** @var Auth $auth */
        $auth = service('auth');

        return $auth->setAuthenticatorAlias($authenticatorAlias);
    }
}

if (! function_exists('user_id')) {
    /**
     * Returns the ID for the current logged in user.
     * Note: For \CodeIgniter\Shield\Entities\User this will always return an int.
     *
     * @return mixed|null
     */
    function user_id()
    {
        return service('auth')->id();
    }
}
