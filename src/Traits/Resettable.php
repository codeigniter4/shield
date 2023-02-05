<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Traits;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Models\UserIdentityModel;

/**
 * Reusable methods to help the
 * enforcing of password resets
 */
trait Resettable
{
    /**
     * Returns true|false based on the value of the
     * force reset column of the user's identity.
     */
    public function requiresPasswordReset(): bool
    {
        $identityModel = model(UserIdentityModel::class);
        $identity      = $identityModel->getIdentityByType($this, Session::ID_TYPE_EMAIL_PASSWORD);

        return $identity->force_reset;
    }

    /**
     * Force password reset
     */
    public function forcePasswordReset(): void
    {
        // Do nothing if user already requires reset
        if ($this->requiresPasswordReset()) {
            return;
        }

        $this->setForceReset(true);
    }

    /**
     * Undo Force password reset
     */
    public function undoForcePasswordReset(): void
    {
        // If user doesn't require password reset, do nothing
        if ($this->requiresPasswordReset() === false) {
            return;
        }

        $this->setForceReset(false);
    }

    /**
     * Set force_reset
     */
    private function setForceReset(bool $value): void
    {
        $value = (int) $value;

        $identityModel = model(UserIdentityModel::class);
        $identityModel->set('force_reset', $value);
        $identityModel->where(['user_id' => $this->id, 'type' => Session::ID_TYPE_EMAIL_PASSWORD]);
        $identityModel->update();
    }
}
