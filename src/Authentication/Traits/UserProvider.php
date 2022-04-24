<?php

namespace Sparks\Shield\Authentication\Traits;

use Sparks\Shield\Entities\User;
use Sparks\Shield\Interfaces\Authenticatable;

trait UserProvider
{
    /**
     * Locates an identity object by ID.
     *
     * @return Authenticatable|null
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Locate a user by the given credentials.
     *
     * @return Authenticatable|null
     */
    public function findByCredentials(array $credentials)
    {
        // Email is stored in an identity so remove that here
        $email = $credentials['email'] ?? null;
        unset($credentials['email']);

        $prefix = $this->db->DBPrefix;

        // any of the credentials used should be case-insensitive
        foreach ($credentials as $key => $value) {
            $this->where("LOWER({$prefix}users.{$key})", strtolower($value));
        }

        if (! empty($email)) {
            $data = $this->select('users.*, auth_identities.secret as email, auth_identities.secret2 as password_hash')
                ->join('auth_identities', 'auth_identities.user_id = users.id')
                ->where('auth_identities.type', 'email_password')
                ->where("LOWER({$prefix}auth_identities.secret)", strtolower($email))
                ->asArray()
                ->first();

            if ($data === null) {
                return null;
            }

            $email = $data['email'];
            unset($data['email']);
            $password_hash = $data['password_hash'];
            unset($data['password_hash']);

            $user                = new User($data);
            $user->email         = $email;
            $user->password_hash = $password_hash;

            return $user;
        }

        return $this->first();
    }

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @return Authenticatable|null
     */
    public function findByRememberToken(int $id, string $token)
    {
        return $this->where([
            'id'          => $id,
            'remember_me' => trim($token),
        ])->first();
    }
}
