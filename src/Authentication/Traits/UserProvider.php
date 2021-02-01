<?php

namespace Sparks\Shield\Authentication\Traits;

trait UserProvider
{
	/**
	 * Locates an identity object by ID.
	 *
	 * @param integer $id
	 *
	 * @return \Sparks\Shield\Interfaces\Authenticatable|null
	 */
	public function findById(int $id)
	{
		return $this->find($id);
	}

	/**
	 * Locate a user by the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return \Sparks\Shield\Interfaces\Authenticatable|null
	 */
	public function findByCredentials(array $credentials)
	{
		// Email is stored in an identity so remove that here
		$email = $credentials['email'] ?? null;
		unset($credentials['email']);

		if (! empty($email))
		{
			$this->select('users.*, auth_identities.secret as email, auth_identities.secret2 as password_hash')
				->join('auth_identities', 'auth_identities.user_id = users.id')
				->where('auth_identities.type', 'email_password');
		}

		return $this->where($credentials)->first();
	}

	/**
	 * Find a user by their ID and "remember-me" token.
	 *
	 * @param integer $id
	 * @param string  $token
	 *
	 * @return \Sparks\Shield\Interfaces\Authenticatable|null
	 */
	public function findByRememberToken(int $id, string $token)
	{
		return $this->where([
			'id'          => $id,
			'remember_me' => trim($token),
		])->first();
	}
}
