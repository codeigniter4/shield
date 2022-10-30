<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Test;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;

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
     * @param bool $pending Whether pending login state or not.
     *
     * @return $this
     */
    public function actingAs(User $user, bool $pending = false): self
    {
        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        if ($pending) {
            $authenticator->startLogin($user);
        } else {
            $authenticator->login($user);
        }

        return $this;
    }
}
