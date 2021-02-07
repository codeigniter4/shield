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
