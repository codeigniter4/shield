<?php

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\Shield\Config\Auth;

/**
 * Class Passwords
 *
 * Provides a central location to handle password
 * related tasks like hashing, verifying, validating, etc.
 *
 * @package CodeIgniter\Shield\Authentication
 */
class Passwords
{
	/**
	 * @var \CodeIgniter\Shield\Config\Auth
	 */
	protected $config;

	public function __construct(Auth $config)
	{
		$this->config = $config;
	}

	/**
	 * Hash a password.
	 *
	 * @param string $password
	 *
	 * @return false|string|null
	 */
	public function hash(string $password)
	{
		if ((defined('PASSWORD_ARGON2I') && $this->config->hashAlgorithm === PASSWORD_ARGON2I)
			||
			(defined('PASSWORD_ARGON2ID') && $this->config->hashAlgorithm === PASSWORD_ARGON2ID)
		)
		{
			$hashOptions = [
				'memory_cost' => $this->config->hashMemoryCost,
				'time_cost'   => $this->config->hashTimeCost,
				'threads'     => $this->config->hashThreads,
			];
		}
		else
		{
			$hashOptions = [
				'cost' => $this->config->hashCost,
			];
		}

		return password_hash(base64_encode(
				hash('sha384', $password, true)
			),
			$this->config->hashAlgorithm,
			$hashOptions
		);
	}

	/**
	 * Verifies a password against a previously hashed password.
	 *
	 * @param string $password The password we're checking
	 * @param string $hash     The previously hashed password
	 */
	public function verify(string $password, string $hash)
	{
		return password_verify(base64_encode(
			hash('sha384', $password, true)
		), $hash);
	}

	/**
	 * Checks to see if a password should be rehashed.
	 *
	 * @param string $hashedPassword
	 *
	 * @return boolean
	 */
	public function needsRehash(string $hashedPassword)
	{
		return password_needs_rehash($hashedPassword, $this->config->hashAlgorithm);
	}
}
