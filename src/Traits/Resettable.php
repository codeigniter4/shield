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
        $identity_model = model(UserIdentityModel::class);
        $identity       = $identity_model->getIdentityByType($this, Session::ID_TYPE_EMAIL_PASSWORD);

        return $identity->force_reset;
    }

    /**
     * Force password reset
     */
    public function forcePasswordReset(): bool
    {
        // Do nothing if user already requires reset
        if ($this->requiresPasswordReset()) {
            return true;
        }

        // Set force_reset to true
        $identity_model = model(UserIdentityModel::class);
        $identity_model->set('force_reset', 1);
        $identity_model->where(['user_id' => $this->id, 'type' => Session::ID_TYPE_EMAIL_PASSWORD]);

        return $identity_model->update();
    }

    /**
     * Undo Force password reset
     */
    public function undoForcePasswordReset(): bool
    {
        // If user doesn't require password reset, do nothing
        if ($this->requiresPasswordReset() === false) {
            return true;
        }

        // Set force_reset to false
        $identity_model = model(UserIdentityModel::class);
        $identity_model->set('force_reset', 0);
        $identity_model->where(['user_id' => $this->id, 'type' => Session::ID_TYPE_EMAIL_PASSWORD]);

        return $identity_model->update();
    }
}
