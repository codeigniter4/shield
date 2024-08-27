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

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;
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
 *
 * @property string|Time|null $last_used_at
 * @property string|null      $secret
 * @property string|null      $secret2
 */
class UserIdentity extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'          => '?integer',
        'force_reset' => 'int-bool',
    ];

    /**
     * @var list<string>
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
