<?php

namespace CodeIgniter\Shield\Authentication\Traits;

use CodeIgniter\Shield\Models\AccessTokenModel;

/**
 * Trait HasAccessTokens
 *
 * Provides functionality needed to generate, revoke,
 * and retrieve Personal Access Tokens.
 *
 * Intended to be used with User models.
 */
trait HasAccessTokens
{
	/**
	 * Generates a new personal access token for this user.
	 *
	 * @param string $name
	 * @param array  $scopes
	 */
	public function generateAccessToken(string $name, array $scopes = ['*'])
	{
		$tokens = model(AccessTokenModel::class);
		helper('text');

		$tokens->insert([
			'user_id' => $this->id,
			'name'    => $name,
			'token'   => hash('sha256', $rawToken = random_string('crypto', 64)),
			'scopes'  => $scopes,
		]);

		$token = $tokens->find($tokens->insertId());

		$token->raw_token = $rawToken;

		return $token;
	}

	/**
	 * Given the token, will retrieve the token to
	 * verify it exists, then delete it.
	 *
	 * @param string $token
	 */
	public function revokeAccessToken(string $token)
	{
		$tokens = model(AccessTokenModel::class);

		return $tokens->where('user_id', $this->id)
					  ->where('token', hash('sha256', $token))
					  ->delete();
	}

	/**
	 * Revokes all access tokens for this user.
	 */
	public function revokeAllAccessTokens()
	{
		$tokens = model(AccessTokenModel::class);

		return $tokens->where('user_id', $this->id)
					  ->delete();
	}

	/**
	 * Retrieves all personal access tokens for this user.
	 *
	 * @return array
	 */
	public function accessTokens(): array
	{
		$tokens = model(AccessTokenModel::class);

		return $tokens->where('user_id', $this->id)
					  ->find();
	}

	/**
	 * Given a raw token, will hash it and attemp to
	 * locate it within the system.
	 *
	 * @param string $token
	 *
	 * @return \CodeIgniter\Shield\Entities\AccessToken|null
	 */
	public function getAccessToken(string $token)
	{
		$tokens = model(AccessTokenModel::class);

		return $tokens->where('user_id', $this->id)
			->where('token', hash('sha256', $token))
			->first();
	}

	/**
	 * Given the ID, returns the given access token.
	 *
	 * @param integer $id
	 *
	 * @return \CodeIgniter\Shield\Entities\AccessToken|null
	 */
	public function getAccessTokenById(int $id)
	{
		$tokens = model(AccessTokenModel::class);

		return $tokens->where('user_id', $this->id)
					  ->where('id', $id)
					  ->first();
	}
}
