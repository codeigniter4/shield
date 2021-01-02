<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity;
use CodeIgniter\Shield\Authentication\Traits\Authenticatable;
use CodeIgniter\Shield\Authentication\Traits\HasAccessTokens;

class User extends Entity implements \CodeIgniter\Shield\Interfaces\Authenticatable
{
	use Authenticatable;
	use HasAccessTokens;

	protected $casts = [
		'active'           => 'boolean',
		'force_pass_reset' => 'boolean',
	];
}
