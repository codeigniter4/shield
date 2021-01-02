<?php

namespace CodeIgniter\Shield\Authentication\Traits;

trait UserProvider
{
	/**
	 * Locates an identity object by ID.
	 *
	 * @param integer $id
	 *
	 * @return \CodeIgniter\Shield\Interfaces\Authenticatable|null
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
	 * @return \CodeIgniter\Shield\Interfaces\Authenticatable|null
	 */
	public function findByCredentials(array $credentials)
	{
		return $this->where($credentials)->first();
	}

	/**
	 * Find a user by their ID and "remember-me" token.
	 *
	 * @param integer $id
	 * @param string  $token
	 *
	 * @return \CodeIgniter\Shield\Interfaces\Authenticatable|null
	 */
	public function findByRememberToken(int $id, string $token)
	{
		return $this->where([
			'id'          => $id,
			'remember_me' => trim($token),
		])->first();
	}
}
