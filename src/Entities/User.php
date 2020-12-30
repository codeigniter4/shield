<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity;
use CodeIgniter\Shield\Authentication\Traits\Authenticatable;

class User extends Entity implements \CodeIgniter\Shield\Interfaces\Authenticatable
{
	use Authenticatable;

	protected $casts = [
		'active'           => 'boolean',
		'force_pass_reset' => 'boolean',
	];
}
