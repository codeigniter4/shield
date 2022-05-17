<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class AccessToken
 *
 * Represents a single Personal Access Token, used
 * for authenticating users for an API.
 */
class AccessToken extends Entity
{
    private ?User $user = null;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'extra'        => 'array',
        'expires'      => 'datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected $datamap = [
        'scopes' => 'extra',
    ];

    /**
     * Returns the user associated with this token.
     */
    public function user(): ?User
    {
        if ($this->user === null) {
            helper('auth');

            $users      = auth()->getProvider();
            $this->user = $users->findById($this->user_id);
        }

        return $this->user;
    }

    /**
     * Determines whether this token grants
     * permission to the $scope
     */
    public function can(string $scope): bool
    {
        if ($this->extra === []) {
            return false;
        }

        // Wildcard present
        if (in_array('*', $this->extra, true)) {
            return true;
        }

        // Check stored scopes
        return in_array($scope, $this->extra, true);
    }

    /**
     * Determines whether this token does NOT
     * grant permission to $scope.
     */
    public function cant(string $scope): bool
    {
        if ($this->extra === []) {
            return true;
        }

        // Wildcard present
        if (in_array('*', $this->extra, true)) {
            return false;
        }

        // Check stored scopes
        return ! in_array($scope, $this->extra, true);
    }
}
