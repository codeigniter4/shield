<?php

namespace Sparks\Shield\Authentication\Traits;

use Sparks\Shield\Entities\AccessToken;
use Sparks\Shield\Models\UserIdentityModel;

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
     */
    public function generateAccessToken(string $name, array $scopes = ['*'])
    {
        $identities = model(UserIdentityModel::class);
        helper('text');

        $identities->insert([
            'type'    => 'access_token',
            'user_id' => $this->id,
            'name'    => $name,
            'secret'  => hash('sha256', $rawToken = random_string('crypto', 64)),
            'extra'   => serialize($scopes),
        ]);

        $token = $identities
            ->asObject(AccessToken::class)
            ->find($identities->getInsertID());

        $token->raw_token = $rawToken;

        return $token;
    }

    /**
     * Given the token, will retrieve the token to
     * verify it exists, then delete it.
     */
    public function revokeAccessToken(string $token)
    {
        return model(UserIdentityModel::class)
            ->where('user_id', $this->id)
            ->where('type', 'access_token')
            ->where('secret', hash('sha256', $token))
            ->delete();
    }

    /**
     * Revokes all access tokens for this user.
     */
    public function revokeAllAccessTokens()
    {
        return model(UserIdentityModel::class)
            ->where('user_id', $this->id)
            ->where('type', 'access_token')
            ->delete();
    }

    /**
     * Retrieves all personal access tokens for this user.
     */
    public function accessTokens(): array
    {
        return model(UserIdentityModel::class)
            ->where('user_id', $this->id)
            ->where('type', 'access_token')
            ->asObject(AccessToken::class)
            ->find();
    }

    /**
     * Given a raw token, will hash it and attemp to
     * locate it within the system.
     *
     * @param string $token
     *
     * @return \Sparks\Shield\Entities\AccessToken|null
     */
    public function getAccessToken(?string $token)
    {
        if (empty($token)) {
            return null;
        }

        $identities = model(UserIdentityModel::class);

        return $identities->where('user_id', $this->id)
            ->where('type', 'access_token')
            ->where('secret', hash('sha256', $token))
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Given the ID, returns the given access token.
     *
     * @return \Sparks\Shield\Entities\AccessToken|null
     */
    public function getAccessTokenById(int $id)
    {
        $tokens = model(UserIdentityModel::class);

        return $tokens->where('user_id', $this->id)
            ->where('type', 'access_token')
            ->where('id', $id)
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Determines whether the user's token grants permissions to $scope.
     * First checks against $this->activeToken, which is set during
     * authentication. If it hasn't been set, returns false.
     */
    public function tokenCan(string $scope): bool
    {
        if (! $this->currentAccessToken() instanceof AccessToken) {
            return false;
        }

        return $this->currentAccessToken()->can($scope);
    }

    /**
     * Determines whether the user's token does NOT grant permissions to $scope.
     * First checks against $this->activeToken, which is set during
     * authentication. If it hasn't been set, returns true.
     */
    public function tokenCant(string $scope): bool
    {
        if (! $this->currentAccessToken() instanceof AccessToken) {
            return true;
        }

        return $this->currentAccessToken()->cant($scope);
    }

    /**
     * Returns the current access token for the user.
     *
     * @return \Sparks\Shield\Entities\AccessToken
     */
    public function currentAccessToken()
    {
        return $this->attributes['activeAccessToken'] ?? null;
    }

    /**
     * Sets the current active token for this user.
     *
     * @param \Sparks\Shield\Entities\AccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken(?AccessToken $accessToken)
    {
        $this->attributes['activeAccessToken'] = $accessToken;

        return $this;
    }
}
