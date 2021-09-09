<?php

namespace Sparks\Shield\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Faker\Generator;
use Sparks\Shield\Entities\Login;

class LoginModel extends Model
{
	protected $table      = 'auth_logins';
	protected $primaryKey = 'id';

	protected $returnType     = Login::class;
	protected $useSoftDeletes = false;

	protected $allowedFields = [
		'ip_address',
		'user_agent',
		'email',
		'user_id',
		'date',
		'success',
	];

	protected $useTimestamps = false;

	protected $validationRules    = [
		'ip_address' => 'required',
		'email'      => 'required',
		'user_agent' => 'permit_empty|string',
		'user_id'    => 'permit_empty|integer',
		'date'       => 'required|valid_date',
	];
	protected $validationMessages = [];
	protected $skipValidation     = false;

	/**
	 * @param string       $email
	 *                          * @param bool        $success
	 * @param string|null  $ipAddress
	 * @param integer|null $userID
	 *
	 * @return \CodeIgniter\Database\BaseResult|false|integer|object|string
	 */
	public function recordLoginAttempt(string $email, bool $success, string $ipAddress = null, string $userAgent = null, int $userID = null)
	{
		return $this->insert([
			'ip_address' => $ipAddress,
			'user_agent' => $userAgent,
			'email'      => $email,
			'user_id'    => $userID,
			'date'       => date('Y-m-d H:i:s'),
			'success'    => (int)$success,
		]);
	}

	/**
	 * Generate a fake login for testing
	 *
	 * @param Generator $faker
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function fake(Generator &$faker)
	{
		return [
			'ip_address' => $faker->ipv4,
			'email'      => $faker->email,
			'user_id'    => null,
			'date'       => Time::parse('-1 day')->toDateTimeString(),
			'success'    => true,
		];
	}
}
