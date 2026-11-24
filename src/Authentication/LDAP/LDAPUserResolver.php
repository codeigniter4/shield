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

namespace CodeIgniter\Shield\Authentication\LDAP;

use Closure;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Exceptions\LDAPException;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use Throwable;

/**
 * Resolves verified directory identities to Shield users.
 */
final readonly class LDAPUserResolver
{
    public const ID_TYPE = 'ldap';

    private UserIdentityModel $identities;

    /**
     * Uses the standard identity model unless the caller supplies one.
     */
    public function __construct(private UserModel $users, ?UserIdentityModel $identities = null)
    {
        $this->identities = $identities ?? model(UserIdentityModel::class);
    }

    /**
     * Resolve an immutable directory ID after credential and access checks.
     *
     * Profile keys must be explicitly mapped to writable UserModel columns.
     * LDAP contact addresses cannot modify the local email/password identity.
     *
     * @param array<string, string|null> $profile
     */
    public function resolve(
        string $connectionId,
        string $externalId,
        array $profile = [],
        bool $provision = false,
        bool $updateProfile = true,
    ): User {
        $key     = $this->identityKey($connectionId, $externalId);
        $profile = $this->validateProfile($profile);

        return $this->atomically(function () use ($connectionId, $key, $profile, $provision, $updateProfile): User {
            $identity = $this->identities->getIdentityBySecret(self::ID_TYPE, $key);

            if ($identity === null) {
                if (! $provision) {
                    throw new LDAPException('user_not_provisioned');
                }

                $username = $profile['username'] ?? null;
                if ($username === null) {
                    throw new LDAPException('invalid_profile');
                }

                $this->checkUsername($username);

                $userId = $this->users->insert($profile);
                if ($userId === true) {
                    throw new LDAPException('synchronization_conflict');
                }

                $user = $this->readBackProfile($userId, $profile);
                $this->createIdentity($connectionId, $key, $userId);

                return $user;
            }

            $user = $this->findUser($identity->user_id);
            if (! $updateProfile || $user->isBanned() || ! $user->isActivated()) {
                return $user;
            }

            $stored = $user->toRawArray();

            foreach ($profile as $field => $value) {
                if (array_key_exists($field, $stored) && $stored[$field] === $value) {
                    unset($profile[$field]);
                }
            }

            if ($profile === []) {
                return $user;
            }

            if (isset($profile['username'])) {
                $this->checkUsername($profile['username'], $identity->user_id);
            }

            $this->users->update($identity->user_id, $profile);

            return $this->readBackProfile($identity->user_id, $profile);
        });
    }

    /**
     * Explicitly associate a verified directory ID with a selected Shield user.
     *
     * The caller must authorize the link and verify the external ID. The
     * resolver never selects the local account by a directory profile value.
     */
    public function link(string $connectionId, string $externalId, int|string $userId): User
    {
        $key = $this->identityKey($connectionId, $externalId);

        return $this->atomically(function () use ($connectionId, $key, $userId): User {
            $user     = $this->findUser($userId);
            $identity = $this->identities->getIdentityBySecret(self::ID_TYPE, $key);

            if ($identity === null) {
                $this->createIdentity($connectionId, $key, $userId);
            } elseif ((string) $identity->user_id !== (string) $user->id) {
                throw new LDAPException('identity_conflict');
            }

            return $user;
        });
    }

    /**
     * Convert AD's mixed-endian, 16-byte objectGUID to a canonical UUID.
     */
    public static function normalizeObjectGuid(string $bytes): string
    {
        if (strlen($bytes) !== 16) {
            throw new LDAPException('invalid_identity');
        }

        return bin2hex(strrev(substr($bytes, 0, 4)))
            . '-' . bin2hex(strrev(substr($bytes, 4, 2)))
            . '-' . bin2hex(strrev(substr($bytes, 6, 2)))
            . '-' . bin2hex(substr($bytes, 8, 2))
            . '-' . bin2hex(substr($bytes, 10));
    }

    private function identityKey(string $connectionId, string $externalId): string
    {
        if (preg_match('/\A[a-zA-Z0-9_-]{1,64}\z/', $connectionId) !== 1 || $externalId === '' || strlen($externalId) > 1024) {
            throw new LDAPException('invalid_identity');
        }

        return hash('sha256', $connectionId . "\0" . $externalId);
    }

    /**
     * @param array<string, string|null> $profile
     *
     * @return array<string, string|null>
     */
    private function validateProfile(array $profile): array
    {
        $reserved = [
            'id', 'userid', 'email', 'password', 'passwordhash', 'secret', 'secret2',
            'identities', 'active', 'status', 'statusmessage', 'forcereset',
            'groups', 'permissions', 'createdat', 'updatedat', 'deletedat', 'lastactive',
        ];

        foreach (array_keys($profile) as $field) {
            $collapsed = str_replace('_', '', strtolower($field));

            if (preg_match('/\A[a-zA-Z][a-zA-Z0-9_]*\z/', $field) !== 1
                || in_array($collapsed, $reserved, true)
                || ($collapsed === 'username' && $field !== 'username')
            ) {
                throw new LDAPException('invalid_profile');
            }
        }

        if (array_key_exists('username', $profile)) {
            $username = $profile['username'];

            if ($username === null
                || trim($username) === ''
                || str_contains($username, "\0")
                || ! mb_check_encoding($username, 'UTF-8')
                || mb_strlen($username, 'UTF-8') > 30
            ) {
                throw new LDAPException('invalid_profile');
            }

            $profile['username'] = strtolower($username);
        }

        return $profile;
    }

    private function checkUsername(string $username, int|string|null $ownerId = null): void
    {
        $owner = $this->users->findByCredentials(['username' => $username]);
        if ($owner !== null && ($ownerId === null || (string) $owner->id !== (string) $ownerId)) {
            throw new LDAPException('identity_conflict');
        }
    }

    private function findUser(int|string $userId): User
    {
        $user = $this->users->findById($userId);
        if ($user === null) {
            throw new LDAPException('identity_conflict');
        }

        return $user;
    }

    /**
     * @param array<string, string|null> $profile
     */
    private function readBackProfile(int|string $userId, array $profile): User
    {
        $user   = $this->findUser($userId);
        $stored = $user->toRawArray();

        foreach ($profile as $field => $value) {
            if (! array_key_exists($field, $stored) || $stored[$field] !== $value) {
                throw new LDAPException('invalid_profile');
            }
        }

        return $user;
    }

    private function createIdentity(string $connectionId, string $key, int|string $userId): void
    {
        $this->identities->create(new UserIdentity([
            'user_id' => $userId,
            'type'    => self::ID_TYPE,
            'name'    => $connectionId,
            'secret'  => $key,
        ]));

        $identity = $this->identities->getIdentityBySecret(self::ID_TYPE, $key);
        if ($identity === null || (string) $identity->user_id !== (string) $userId) {
            throw new LDAPException('synchronization_conflict');
        }
    }

    /**
     * @param Closure(): User $operation
     */
    private function atomically(Closure $operation): User
    {
        try {
            $database = $this->users->builder()->db();
            // CodeIgniter does not independently roll back nested transactions.
            if ($database !== $this->identities->builder()->db()
                || $database->transDepth !== 0
                || ! $database->transStatus()
                || ! $database->transBegin()
            ) {
                throw new LDAPException('synchronization_conflict');
            }

            $strict = $database->transStrict;
            $database->transStrict(false);

            try {
                $user = $operation();
                if (! $database->transComplete()) {
                    throw new LDAPException('synchronization_conflict');
                }

                return $user;
            } finally {
                try {
                    if ($database->transDepth !== 0) {
                        $this->rollBack($database);
                    }
                } finally {
                    $database->transStrict($strict);
                }
            }
        } catch (LDAPException $e) {
            throw $e;
        } catch (Throwable) {
            // Model exceptions can contain SQL with directory and local account data.
            throw new LDAPException('synchronization_conflict');
        }
    }

    private function rollBack(BaseConnection $database): void
    {
        if ($database->transStatus()) {
            $database->transRollback();
        } else {
            // Completing a failed non-strict transaction rolls back and resets its status.
            $database->transComplete();
        }
    }
}
