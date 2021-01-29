<?php

namespace Sparks\Shield\Models;

use CodeIgniter\Model;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Interfaces\UserProvider;
use Sparks\Shield\Authentication\Traits\UserProvider as UserProviderTrait;
use Faker\Generator;

class UserModel extends Model implements UserProvider
{
	use UserProviderTrait;

	protected $table      = 'users';
	protected $primaryKey = 'id';

	protected $returnType     = User::class;
	protected $useSoftDeletes = true;

	protected $allowedFields = [
		'email',
		'username',
		'password_hash',
		'reset_hash',
		'reset_at',
		'reset_expires',
		'activate_hash',
		'status',
		'status_message',
		'active',
		'force_pass_reset',
		'deleted_at',
	];

	protected $useTimestamps = true;

	public function fake(Generator &$faker)
	{
		return [
			'email'                => $faker->email,
			'username'             => $faker->userName,
			'password_hash'        => password_hash('secret', PASSWORD_DEFAULT),
			'active'               => true,
			'force_password_reset' => false,
		];
	}
}
