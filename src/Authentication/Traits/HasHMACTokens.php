<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\Traits;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Models\UserIdentityModel;
use ReflectionException;

/**
 * Trait HasHMACTokens
 *
 * Provides functionality needed to generate, revoke,
 * and retrieve Personal Access Tokens.
 *
 * Intended to be used with User entities.
 */
trait HasHMACTokens
{
    /**
     * The current access token for the user.
     */
    private ?AccessToken $currentAccessToken = null;

    /**
     * Generates a new personal HMAC token for this user.
     *
     * @param string   $name   Token name
     * @param string[] $scopes Permissions the token grants
     *
     * @throws ReflectionException
     */
    public function generateHMACToken(string $name, array $scopes = ['*']): AccessToken
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->generateHMACToken($this, $name, $scopes);
    }

    /**
     * Delete any HMAC tokens for the given raw token.
     */
    public function revokeHMACToken(string $rawToken): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->revokeHMACToken($this, $rawToken);
    }

    /**
     * Revokes all HMAC tokens for this user.
     */
    public function revokeAllHMACTokens(): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->revokeAllHMACTokens($this);
    }

    /**
     * Retrieves all personal HMAC tokens for this user.
     *
     * @return AccessToken[]
     */
    public function hmacTokens(): array
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAllHMACTokens($this);
    }

    /**
     * Given a raw token, it will locate it within the system.
     */
    public function getHmacToken(?string $rawToken): ?AccessToken
    {
        if (empty($rawToken)) {
            return null;
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getHMACToken($this, $rawToken);
    }

    /**
     * Given the ID, returns the given access token.
     */
    public function getHMACTokenById(int $id): ?AccessToken
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getHMACTokenById($id, $this);
    }

    //    /**
    //     * Determines whether the user's token grants permissions to $scope.
    //     * First checks against $this->activeToken, which is set during
    //     * authentication. If it hasn't been set, returns false.
    //     */
    //    public function tokenCan(string $scope): bool
    //    {
    //        if (! $this->currentAccessToken() instanceof AccessToken) {
    //            return false;
    //        }
    //
    //        return $this->currentAccessToken()->can($scope);
    //    }
    //
    //    /**
    //     * Determines whether the user's token does NOT grant permissions to $scope.
    //     * First checks against $this->activeToken, which is set during
    //     * authentication. If it hasn't been set, returns true.
    //     */
    //    public function tokenCant(string $scope): bool
    //    {
    //        if (! $this->currentAccessToken() instanceof AccessToken) {
    //            return true;
    //        }
    //
    //        return $this->currentAccessToken()->cant($scope);
    //    }

    /**
     * Returns the current HMAC token for the user.
     */
    public function currentHMACToken(): ?AccessToken
    {
        return $this->currentAccessToken;
    }

    /**
     * Sets the current active token for this user.
     *
     * @return $this
     */
    public function setHMACToken(?AccessToken $accessToken): self
    {
        $this->currentAccessToken = $accessToken;

        return $this;
    }
}
