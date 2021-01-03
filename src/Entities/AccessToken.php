<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity;

/**
 * Class AccessToken
 *
 * Represents a single Personal Access Token, used
 * for authenticating users for an API.
 *
 * @package CodeIgniter\Shield\Entities
 */
class AccessToken extends Entity
{
	protected $casts = [
		'last_used_at' => 'datetime',
		'scopes'       => 'array',
		'expires'      => 'datetime',
	];

	/**
	 * Returns the user associated with this token.
	 *
	 * @return mixed
	 */
	public function user()
	{
		if (empty($this->attributes['user']))
		{
			helper('auth');
			$users                    = auth()->getProvider();
			$this->attributes['user'] = $users->findById($this->user_id);
		}

		return $this->attributes['user'];
	}

	/**
	 * Determines whether this token grants
	 * permission to the $scope
	 *
	 * @param string $scope
	 *
	 * @return boolean
	 */
	public function can(string $scope): bool
	{
		if (empty($this->scopes))
		{
			return false;
		}

		// Wildcard present
		if (in_array('*', $this->scopes))
		{
			return true;
		}

		// Check stored scopes
		return in_array($scope, $this->scopes);
	}

	/**
	 * Determines whether this token does NOT
	 * grant permission to $scope.
	 *
	 * @param string $scope
	 *
	 * @return boolean
	 */
	public function cant(string $scope): bool
	{
		if (empty($this->scopes))
		{
			return true;
		}

		// Wildcard present
		if (in_array('*', $this->scopes))
		{
			return false;
		}

		// Check stored scopes
		return ! in_array($scope, $this->scopes);
	}
}
