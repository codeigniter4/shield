<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Model;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use Faker\Generator;

class UserIdentityModel extends Model
{
    protected $table          = 'auth_identities';
    protected $primaryKey     = 'id';
    protected $returnType     = UserIdentity::class;
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'user_id',
        'type',
        'name',
        'secret',
        'secret2',
        'expires',
        'extra',
        'force_reset',
        'last_used_at',
    ];
    protected $useTimestamps = true;

    public function getAccessTokenByRawToken(string $rawToken): ?AccessToken
    {
        return $this
            ->where('type', 'access_token')
            ->where('secret', hash('sha256', $rawToken))
            ->asObject(AccessToken::class)
            ->first();
    }

    public function getAccessToken(User $user, string $rawToken): ?AccessToken
    {
        return $this->where('user_id', $user->getAuthId())
            ->where('type', 'access_token')
            ->where('secret', hash('sha256', $rawToken))
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Given the ID, returns the given access token.
     *
     * @param int|string $id
     */
    public function getAccessTokenById($id, User $user): ?AccessToken
    {
        return $this->where('user_id', $user->getAuthId())
            ->where('type', 'access_token')
            ->where('id', $id)
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * @return AccessToken[]
     */
    public function getAllAccessTokens(User $user): array
    {
        return $this
            ->where('user_id', $user->getAuthId())
            ->where('type', 'access_token')
            ->orderBy($this->primaryKey)
            ->asObject(AccessToken::class)
            ->findAll();
    }

    public function getIdentityBySecret(string $type, ?string $secret): ?UserIdentity
    {
        if ($secret === null) {
            return null;
        }

        return $this->where('type', $type)
            ->where('secret', $secret)
            ->first();
    }

    /**
     * @return UserIdentity[]
     */
    public function getIdentities(User $user): array
    {
        return $this->where('user_id', $user->getAuthId())->orderBy($this->primaryKey)->findAll();
    }

    /**
     * @param int[]|string[] $userIds
     *
     * @return UserIdentity[]
     */
    public function getIdentitiesByUserIds(array $userIds): array
    {
        return $this->whereIn('user_id', $userIds)->orderBy($this->primaryKey)->findAll();
    }

    public function getIdentityByType(User $user, string $type): ?UserIdentity
    {
        return $this->where('user_id', $user->getAuthId())
            ->where('type', $type)
            ->first();
    }

    /**
     * @param string[] $types
     *
     * @return UserIdentity[]
     */
    public function getIdentitiesByTypes(User $user, array $types): array
    {
        if ($types === []) {
            return [];
        }

        return $this->where('user_id', $user->getAuthId())
            ->whereIn('type', $types)
            ->orderBy($this->primaryKey)
            ->findAll();
    }

    public function deleteIdentitiesByType(User $user, string $type): void
    {
        $this->where('user_id', $user->getAuthId())
            ->where('type', $type)
            ->delete();
    }

    /**
     * Delete any access tokens for the given raw token.
     */
    public function revokeAccessToken(User $user, string $rawToken)
    {
        return $this->where('user_id', $user->getAuthId())
            ->where('type', 'access_token')
            ->where('secret', hash('sha256', $rawToken))
            ->delete();
    }

    /**
     * Revokes all access tokens for this user.
     */
    public function revokeAllAccessTokens(User $user)
    {
        return $this->where('user_id', $user->getAuthId())
            ->where('type', 'access_token')
            ->delete();
    }

    public function fake(Generator &$faker): UserIdentity
    {
        return new UserIdentity([
            'user_id'      => fake(UserModel::class)->id,
            'type'         => 'email_password',
            'name'         => null,
            'secret'       => 'info@example.com',
            'secret2'      => password_hash('secret', PASSWORD_DEFAULT),
            'expires'      => null,
            'extra'        => null,
            'force_reset'  => false,
            'last_used_at' => null,
        ]);
    }
}
