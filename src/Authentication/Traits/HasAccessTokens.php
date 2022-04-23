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
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->revokeAccessToken($this->id, $token);
    }

    /**
     * Revokes all access tokens for this user.
     */
    public function revokeAllAccessTokens()
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->revokeAllAccessTokens($this->id);
    }

    /**
     * Retrieves all personal access tokens for this user.
     *
     * @return AccessToken[]
     */
    public function accessTokens(): array
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAllAccessTokens($this->id);
    }

    /**
     * Given a raw token, will hash it and attempt to
     * locate it within the system.
     *
     * @param string $token
     *
     * @return AccessToken|null
     */
    public function getAccessToken(?string $token)
    {
        if (empty($token)) {
            return null;
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAccessToken($this->id, $token);
    }

    /**
     * Given the ID, returns the given access token.
     *
     * @return AccessToken|null
     */
    public function getAccessTokenById(int $id)
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAccessTokenById($id, $this->id);
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
     * @return AccessToken
     */
    public function currentAccessToken()
    {
        return $this->attributes['activeAccessToken'] ?? null;
    }

    /**
     * Sets the current active token for this user.
     *
     * @param AccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken(?AccessToken $accessToken)
    {
        $this->attributes['activeAccessToken'] = $accessToken;

        return $this;
    }
}
