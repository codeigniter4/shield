<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\Shield\Interfaces\Authenticatable;

/**
 * Class AccessToken
 *
 * Represents a single Personal Access Token, used
 * for authenticating users for an API.
 */
class AccessToken extends Entity
{
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
    public function user(): ?Authenticatable
    {
        if (empty($this->attributes['user'])) {
            helper('auth');
            $users                    = auth()->getProvider();
            $this->attributes['user'] = $users->findById($this->user_id);
        }

        return $this->attributes['user'];
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
