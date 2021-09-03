<?php

if (! defined('auth'))
{
	/**
	 * Provides convenient access to the main Auth class
	 * for CodeIgniter Shield.
	 */
	function auth(string $authenticator = null)
	{
		return service('auth')->setHandler($authenticator);
	}
}

if (! function_exists('user_id'))
{
	/**
	 * Returns the ID for the current logged in user.
	 * Note: For \Sparks\Shield\Entities\User this will always return an int.
	 *
	 * @return mixed|null
	 */
	function user_id()
	{
		return service('auth')->id();
	}
}
