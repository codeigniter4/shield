<?php

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\Shield\Config\Auth as AuthConfig;
use CodeIgniter\Shield\Interfaces\Authenticatable;

interface AuthenticatorInterface
{
	/**
	 * Attempts to authenticate a user with the given $credentials.
	 * Logs the user in with a successful check.
	 *
	 * @param array $credentials
	 * @param bool  $remember
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Shield\Authentication\AuthenticationException
	 */
	public function attempt(array $credentials, bool $remember=false);

	/**
	 * Checks a user's $credentials to see if they match an
	 * existing user.
	 *
	 * @param array $credentials
	 *
	 * @return mixed
	 */
	public function check(array $credentials);

	/**
	 * Checks if the user is currently logged in.
	 *
	 * @return boolean
	 */
	public function loggedIn(): bool;

	/**
	 * Logs the given user in.
	 *
	 * @param Authenticatable $user
	 * @param boolean         $remember
	 *
	 * @return mixed
	 */
	public function login(Authenticatable $user, bool $remember = false);

	/**
	 * Logs a user in based on their ID.
	 *
	 * @param integer $userId
	 * @param boolean $remember
	 *
	 * @return mixed
	 */
	public function loginById(int $userId, bool $remember = false);

	/**
	 * Logs the current user out.
	 *
	 * @return mixed
	 */
	public function logout();

	/**
	 * Removes any remember-me tokens, if applicable.
	 *
	 * @param int|null $id
	 *
	 * @return mixed
	 */
	public function forget(?int $id);

	/**
	 * Returns the currently logged in user.
	 *
	 * @return Authenticatable|null
	 */
	public function getUser();
}
