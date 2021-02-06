<?php

namespace Sparks\Shield\Authentication\Handlers;

use Sparks\Shield\Authentication\AuthenticatorInterface;
use Sparks\Shield\Interfaces\Authenticatable;
use Sparks\Shield\Result;

/**
 * Class Pipeline
 *
 * This Authentication handler will run through all of the other
 * defined authenticators one at a time until either a successful
 * result is returned, or none of them find a match.
 *
 * This is useful for using the same endpoints for your SPA
 * as well as a mobile app without having to specifically
 * worry about which one you need.
 *
 * @package Sparks\Shield\Authentication\Handlers
 */
class Pipeline implements AuthenticatorInterface
{
	/**
	 * Caches instances of the other authenticators
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * Attempts to authenticate a user with the given $credentials.
	 * Logs the user in with a successful check.
	 *
	 * @param array $credentials
	 *
	 * @return mixed
	 * @throws \Sparks\Shield\Authentication\AuthenticationException
	 */
	public function attempt(array $credentials)
	{
		return $this->handle('attempt', $credentials);
	}

	/**
	 * Checks a user's $credentials to see if they match an
	 * existing user.
	 *
	 * @param array $credentials
	 *
	 * @return mixed
	 */
	public function check(array $credentials)
	{
		return $this->handle('check', $credentials);
	}

	/**
	 * Checks if the user is currently logged in.
	 *
	 * @return boolean
	 */
	public function loggedIn(): bool
	{
		return $this->handle('loggedIn');
	}

	/**
	 * Logs the given user in.
	 *
	 * @param Authenticatable $user
	 *
	 * @return mixed
	 */
	public function login(Authenticatable $user)
	{
		return $this->handle('login', $user);
	}

	/**
	 * Logs a user in based on their ID.
	 *
	 * @param integer $userId
	 *
	 * @return mixed
	 */
	public function loginById(int $userId)
	{
		return $this->handle('loginById', $userId);
	}

	/**
	 * Logs the current user out.
	 *
	 * @return mixed
	 */
	public function logout()
	{
		return $this->handle('logout');
	}

	/**
	 * Returns the currently logged in user.
	 *
	 * @return Authenticatable|null
	 */
	public function getUser()
	{
		return $this->handle('getUser');
	}

	/**
	 * Provide magic function-access to handlers to save use
	 * from repeating code here, and to allow them have their
	 * own, additional, features on top of the required ones,
	 * like "remember-me" functionality.
	 *
	 * @param $method
	 * @param $args
	 *
	 * @throws \Sparks\Shield\Authentication\AuthenticationException
	 */
	public function __call($method, $args)
	{
		foreach ($this->instances as $instance)
		{
			if (method_exists($instance, $method))
			{
				return $instance->{$method}(...$args);
			}
		}
	}

	/**
	 * Loads up instances of our other authenticators
	 * if we haven't already done it.
	 */
	protected function init()
	{
		if (! empty($this->instances))
		{
			return $this;
		}

		helper('auth');
		$thisClass = get_class($this);
		$config    = config('Auth');

		foreach ($config->authenticators as $alias => $className)
		{
			if ($className === $thisClass)
			{
				continue;
			}

			$this->instances[$alias] = new $className($config, auth()->getProvider());
		}

		return $this;
	}

	/**
	 *
	 *
	 * @param string $method
	 * @param mixed  ...$params
	 *
	 * @return mixed
	 */
	protected function handle(string $method, ...$params)
	{
		$this->init();

		foreach ($this->instances as $instance)
		{
			$result = $instance->$method(...$params);

			if (is_bool($result) && $result === true)
			{
				return $result;
			}

			if ($result instanceof Result && $result->isOK())
			{
				return $result;
			}
		}

		// Result is not successful, but let the call know.
		return $result;
	}
}
