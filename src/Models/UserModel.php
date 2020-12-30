<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Model;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Interfaces\UserProvider;
use Faker\Generator;

class UserModel extends Model implements UserProvider
{
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

	/**
	 * Locates an identity object by ID.
	 *
	 * @param integer $id
	 *
	 * @return \CodeIgniter\Shield\Interfaces\Authenticatable|null
	 */
	public function findById(int $id)
	{
		return $this->find($id);
	}

	/**
	 * Locate a user by the given credentials.
	 *
	 * @param array $credentials
	 *
	 * @return \CodeIgniter\Shield\Interfaces\Authenticatable|null
	 */
	public function findByCredentials(array $credentials)
	{
		return $this->where($credentials)->first();
	}

	/**
	 * Find a user by their ID and "remember-me" token.
	 *
	 * @param integer $id
	 * @param string  $token
	 *
	 * @return \CodeIgniter\Shield\Interfaces\Authenticatable|null
	 */
	public function findByRememberToken(int $id, string $token)
	{
		return $this->where([
			'id'          => $id,
			'remember_me' => trim($token),
		])->first();
	}

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
