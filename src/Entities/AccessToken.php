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
}
