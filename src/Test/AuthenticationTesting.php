<?php

namespace CodeIgniter\Shield\Test;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Interfaces\Authenticatable;

/**
 * Trait AuthenticationTesting
 *
 * Helper methods for testing using Shield.
 */
trait AuthenticationTesting
{
    /**
     * Logs the user for testing purposes.
     *
     * @return $this
     */
    public function actingAs(Authenticatable $user)
    {
        // Ensure the helper is loaded during tests.
        helper('auth');

        auth('session')->login($user);

        return $this;
    }
}
