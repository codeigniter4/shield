<?php

namespace Sparks\Shield\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
	protected $table      = 'auth_logins';
	protected $primaryKey = 'id';

	protected $returnType     = 'object';
	protected $useSoftDeletes = false;

	protected $allowedFields = [
		'ip_address',
		'email',
		'user_id',
		'date',
		'success',
	];

	protected $useTimestamps = false;

	protected $validationRules    = [
		'ip_address' => 'required',
		'email'      => 'required',
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
	public function recordLoginAttempt(string $email, bool $success, string $ipAddress = null, int $userID = null)
	{
		return $this->insert([
			'ip_address' => $ipAddress,
			'email'      => $email,
			'user_id'    => $userID,
			'date'       => date('Y-m-d H:i:s'),
			'success'    => (int)$success,
		]);
	}
}
