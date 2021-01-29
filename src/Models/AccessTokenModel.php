<?php

namespace Sparks\Shield\Models;

use CodeIgniter\Model;
use Sparks\Shield\Entities\AccessToken;
use Faker\Generator;

/**
 * Class AccessTokenModel
 *
 * Provides database access to personal access tokens.
 *
 * @package Sparks\Shield\Models
 */
class AccessTokenModel extends Model
{
	protected $table      = 'auth_access_tokens';
	protected $primaryKey = 'id';

	protected $returnType     = AccessToken::class;
	protected $useSoftDeletes = false;

	protected $allowedFields = [
		'user_id',
		'name',
		'token',
		'last_used_at',
		'scopes',
	];

	protected $useTimestamps = true;

	public function fake(Generator &$faker)
	{
		helper('text');
		$rawToken = random_string('crypt', 64);

		return [
			'user_id'   => fake(UserModel::class)->id,
			'name'      => $faker->streetName,
			'token'     => hash('sha256', $rawToken),
			'raw_token' => $rawToken, // Only normally available on newly created record.
		];
	}
}
