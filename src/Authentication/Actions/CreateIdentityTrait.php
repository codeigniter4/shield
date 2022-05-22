<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\DatabaseException;
use CodeIgniter\Shield\Models\UserIdentityModel;

/**
 * Create an identity for auth action
 */
trait CreateIdentityTrait
{
    /**
     * Create an identity with 6 digits code for auth action
     */
    private function createCodeIdentity(User $user, string $name, string $extra): string
    {
        assert($user->id !== null);

        helper('text');

        /** @var UserIdentityModel $userIdentityModel */
        $userIdentityModel = model(UserIdentityModel::class);

        // Delete any previous identities for action
        $userIdentityModel->deleteIdentitiesByType($user, $this->type);

        // Create an identity for the action
        $maxTry = 5;
        $data   = [
            'user_id' => $user->id,
            'type'    => $this->type,
            'name'    => $name,
            'extra'   => $extra,
        ];

        while (true) {
            $data['secret'] = $this->generateSecretCode();

            try {
                $userIdentityModel->create($data);

                break;
            } catch (DatabaseException $e) {
                $maxTry--;

                if ($maxTry === 0) {
                    throw $e;
                }
            }
        }

        return $data['secret'];
    }

    private function generateSecretCode(): string
    {
        return random_string('nozero', 6);
    }
}
