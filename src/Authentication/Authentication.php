<?php

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\Shield\Config\Auth as AuthConfig;

class Authentication
{
	/**
	 * Instantiated handler objects,
	 * stored by handler alias.
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * @var \CodeIgniter\Shield\Interfaces\UserProvider
	 */
	protected $userProvider;

	/**
	 * @var \CodeIgniter\Shield\Config\Auth
	 */
	protected $config;

	public function __construct(AuthConfig $config)
	{
		$this->config = $config;
	}

	/**
	 * Returns an instance of the specified handler.
	 *
	 * You can pass 'default' as the handler and it
	 * will return an instance of the first handler specified
	 * in the Auth config file.
	 *
	 * @param string $handler
	 *
	 * @return mixed
	 */
	public function factory(string $handler = 'default')
	{
		// Determine actual handler name
		$handler = $handler === 'default'
			? key($this->config->authenticators)
			: $handler;

		// Return the cached instance if we have it
		if (! empty($this->instances[$handler]))
		{
			return $this->instances[$handler];
		}

		// Otherwise, try to create a new instance.
		if (! array_key_exists($handler, $this->config->authenticators))
		{
			throw AuthenticationException::forUnknownHandler($handler);
		}

		$className = $this->config->authenticators[$handler];

		$property    = "{$handler}Config";
		$localConfig = property_exists($this->config, $property)
			? $this->config->$property
			: $this->config;

		$this->instances[$handler] = new $className($localConfig, $this->getProvider());

		return $this->instances[$handler];
	}

	/**
	 * Returns a shared instance of the User Provider.
	 *
	 * @return \CodeIgniter\Shield\Interfaces\UserProvider|mixed
	 */
	protected function getProvider()
	{
		if ($this->userProvider !== null)
		{
			return $this->userProvider;
		}

		if (! property_exists($this->config, 'userProvider'))
		{
			throw AuthenticationException::forUnknownUserProvider();
		}

		$className          = $this->config->userProvider;
		$this->userProvider = new $className();

		return $this->userProvider;
	}
}
