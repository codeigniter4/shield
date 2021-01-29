<?php

namespace Sparks\Shield\Authentication;

use Sparks\Shield\Config\Auth as AuthConfig;
use Sparks\Shield\Interfaces\Authenticatable;

interface AuthenticatorInterface
{
	/**
	 * Attempts to authenticate a user with the given $credentials.
	 * Logs the user in with a successful check.
	 *
	 * @param array $credentials
	 *
	 * @return mixed
	 * @throws \Sparks\Shield\Authentication\AuthenticationException
	 */
	public function attempt(array $credentials);

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
	 *
	 * @return mixed
	 */
	public function login(Authenticatable $user);

	/**
	 * Logs a user in based on their ID.
	 *
	 * @param integer $userId
	 *
	 * @return mixed
	 */
	public function loginById(int $userId);

	/**
	 * Logs the current user out.
	 *
	 * @return mixed
	 */
	public function logout();

	/**
	 * Returns the currently logged in user.
	 *
	 * @return Authenticatable|null
	 */
	public function getUser();
}
