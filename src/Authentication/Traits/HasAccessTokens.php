<?php

namespace CodeIgniter\Shield\Authentication\Traits;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Models\AccessTokenModel;

/**
 * Trait HasAccessTokens
 *
 * Provides functionality needed to generate, revoke,
 * and retrieve Personal Access Tokens.
 *
 * Intended to be used with User entities.
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
	public function getAccessToken(?string $token)
	{
		if (empty($token))
		{
			return null;
		}

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

	/**
	 * Determines whether the user's token grants permissions to $scope.
	 * First checks against $this->activeToken, which is set during
	 * authentication. If it hasn't been set, returns false.
	 *
	 * @param string $scope
	 *
	 * @return boolean
	 */
	public function tokenCan(string $scope): bool
	{
		if (! $this->currentAccessToken() instanceof AccessToken)
		{
			return false;
		}

		return $this->currentAccessToken()->can($scope);
	}

	/**
	 * Determines whether the user's token does NOT grant permissions to $scope.
	 * First checks against $this->activeToken, which is set during
	 * authentication. If it hasn't been set, returns true.
	 *
	 * @param string $scope
	 *
	 * @return boolean
	 */
	public function tokenCant(string $scope): bool
	{
		if (! $this->currentAccessToken() instanceof AccessToken)
		{
			return true;
		}

		return $this->currentAccessToken()->cant($scope);
	}

	/**
	 * Returns the current access token for the user.
	 *
	 * @return \CodeIgniter\Shield\Entities\AccessToken
	 */
	public function currentAccessToken()
	{
		return $this->attributes['activeAccessToken'] ?? null;
	}

	/**
	 * Sets the current active token for this user.
	 *
	 * @param \CodeIgniter\Shield\Entities\AccessToken $accessToken
	 *
	 * @return $this
	 */
	public function withAccessToken(?AccessToken $accessToken)
	{
		$this->attributes['activeAccessToken'] = $accessToken;

		return $this;
	}
}
