<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Authentication\Traits;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Models\UserIdentityModel;
use InvalidArgumentException;

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
    private ?AccessToken $currentAccessToken = null;

    /**
     * Generates a new personal access token for this user.
     *
     * @param string       $name      Token name
     * @param list<string> $scopes    Permissions the token grants
     * @param Time|null    $expiresAt Expiration date
     *
     * @throws InvalidArgumentException
     */
    public function generateAccessToken(string $name, array $scopes = ['*'], ?Time $expiresAt = null): AccessToken
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->generateAccessToken($this, $name, $scopes, $expiresAt);
    }

    /**
     * Delete any access tokens for the given raw token.
     */
    public function revokeAccessToken(string $rawToken): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->revokeAccessToken($this, $rawToken);
    }

    /**
     * Delete any access tokens for the given secret token.
     */
    public function revokeAccessTokenBySecret(string $secretToken): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->revokeAccessTokenBySecret($this, $secretToken);
    }

    /**
     * Revokes all access tokens for this user.
     */
    public function revokeAllAccessTokens(): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->revokeAllAccessTokens($this);
    }

    /**
     * Retrieves all personal access tokens for this user.
     *
     * @return list<AccessToken>
     */
    public function accessTokens(): array
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAllAccessTokens($this);
    }

    /**
     * Given a raw token, will hash it and attempt to
     * locate it within the system.
     */
    public function getAccessToken(?string $rawToken): ?AccessToken
    {
        if ($rawToken === null || $rawToken === '') {
            return null;
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAccessToken($this, $rawToken);
    }

    /**
     * Given the ID, returns the given access token.
     */
    public function getAccessTokenById(int $id): ?AccessToken
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getAccessTokenById($id, $this);
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
     */
    public function currentAccessToken(): ?AccessToken
    {
        return $this->currentAccessToken;
    }

    /**
     * Sets the current active token for this user.
     *
     * @return $this
     */
    public function setAccessToken(?AccessToken $accessToken): self
    {
        $this->currentAccessToken = $accessToken;

        return $this;
    }

    /**
     * Checks if the provided Access Token is expired.
     */
    public function isAccessTokenExpired(AccessToken $accessToken): bool
    {
        return $accessToken->expires instanceof Time && $accessToken->expires->isBefore(Time::now());
    }

    /**
     * Sets an expiration for Access Tokens by ID.
     *
     * @param int  $id        AccessTokens ID
     * @param Time $expiresAt Expiration date
     *
     * @return bool Returns true if expiration date is set or updated.
     */
    public function updateAccessTokenExpiration(int $id, Time $expiresAt): bool
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);
        $result        = $identityModel->setIdentityExpirationById($id, $this, $expiresAt);

        if ($result) {
            // refresh currentAccessToken with updated data
            $this->currentAccessToken = $identityModel->getAccessTokenById($id, $this);
        }

        return $result;
    }

    /**
     * Removes the expiration date for Access Tokens by ID.
     *
     * @param int $id AccessTokens ID
     *
     * @return bool Returns true if expiration date is set or updated.
     */
    public function removeAccessTokenExpiration(int $id): bool
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);
        $result        = $identityModel->setIdentityExpirationById($id, $this);

        if ($result) {
            // refresh currentAccessToken with updated data
            $this->currentAccessToken = $identityModel->getAccessTokenById($id, $this);
        }

        return $result;
    }

    /**
     * Checks if the access token has a set expiration date
     */
    public function canAccessTokenExpire(AccessToken $accessToken): bool
    {
        return $accessToken->expires !== null;
    }
}
