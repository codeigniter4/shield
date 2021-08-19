<?php

namespace Sparks\Shield\Entities;

use CodeIgniter\Entity;

class Login extends Entity
{
	protected $casts = [
		'date'    => 'datetime',
		'success' => 'boolean',
	];

}
