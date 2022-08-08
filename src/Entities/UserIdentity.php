<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Shield\Authentication\Passwords;

/**
 * Class UserIdentity
 *
 * Represents a single set of user identity credentials.
 * For the base Shield system, this would be one of the following:
 *  - password
 *  - reset hash
 *  - access token
 *
 * This can also be used to store credentials for social logins,
 * OAUTH or JWT tokens, etc. A user can have multiple of each,
 * though a Authenticator may want to enforce only one exists for that
 * user, like a password.
 */
class UserIdentity extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'force_reset' => 'int_bool',
    ];

    /**
     * @var string[]
     * @phpstan-var list<string>
     * @psalm-var list<string>
     */
    protected $dates = [
        'expires',
        'last_used_at',
    ];

    /**
     * Uses password-strength hashing to hash
     * a given value for the 'secret'.
     */
    public function hashSecret(string $value): UserIdentity
    {
        /** @var Passwords $passwords */
        $passwords = service('passwords');

        $this->attributes['secret'] = $passwords->hash($value);

        return $this;
    }
}
