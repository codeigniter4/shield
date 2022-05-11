<?php

namespace CodeIgniter\Shield\Authentication\Traits;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Models\UserIdentityModel;

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
     * The current access token for the user.
     */
    private ?AccessToken $activeAccessToken = null;

    /**
     * Generates a new personal access token for this user.
     */
    public function generateAccessToken(string $name, array $scopes = ['*'])
    {
        helper('text');

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->insert([
            'type'    => 'access_token',
            'user_id' => $this->id,
            'name'    => $name,
            'secret'  => hash('sha256', $rawToken = random_string('crypto', 64)),
            'extra'   => serialize($scopes),
        ]);

        /** @var AccessToken $token */
        $token = $identityModel
            ->asObject(AccessToken::class)
            ->find($identityModel->getInsertID());

        $token->raw_token = $rawToken;

        return $token;
    }

    /**
     * Delete any access tokens for the given raw token.
     */
    public function revokeAccessToken(string $rawToken)
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->revokeAccessToken($this->id, $rawToken);
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
     */
    public function getAccessToken(?string $rawToken): ?AccessToken
    {
        if (empty($rawToken)) {
            return null;
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAccessToken($this->id, $rawToken);
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
        return $this->activeAccessToken;
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
        $this->activeAccessToken = $accessToken;

        return $this;
    }
}
