<?php

namespace CodeIgniter\Shield\Authentication\Traits;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Interfaces\Authenticatable;

/**
 * Intended to be used with UserProvider (UserModel).
 */
trait UserProvider
{
    /**
     * Locates a User object by ID.
     *
     * @param int|string $id
     */
    public function findById($id): ?Authenticatable
    {
        return $this->find($id);
    }

    /**
     * Locate a User object by the given credentials.
     *
     * @param array<string, string> $credentials
     */
    public function findByCredentials(array $credentials): ?Authenticatable
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
     * Find a User object by their ID and "remember-me" token.
     *
     * @param int|string $id
     */
    public function findByRememberToken($id, string $token): ?Authenticatable
    {
        return $this->where([
            'id'          => $id,
            'remember_me' => trim($token),
        ])->first();
    }
}
