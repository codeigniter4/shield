<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Model;
use CodeIgniter\Shield\Entities\AccessToken;
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

    /**
     * @param int|string $userId
     */
    public function getAccessToken($userId, string $rawToken): ?AccessToken
    {
        return $this->where('user_id', $userId)
            ->where('type', 'access_token')
            ->where('secret', hash('sha256', $rawToken))
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Given the ID, returns the given access token.
     *
     * @param int|string $id
     * @param int|string $userId
     */
    public function getAccessTokenById($id, $userId): ?AccessToken
    {
        return $this->where('user_id', $userId)
            ->where('type', 'access_token')
            ->where('id', $id)
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * @param int|string $userId
     *
     * @return AccessToken[]
     */
    public function getAllAccessTokens($userId): array
    {
        return $this
            ->where('user_id', $userId)
            ->where('type', 'access_token')
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
     * @param int|string $userId
     *
     * @return UserIdentity[]
     */
    public function getIdentities($userId): array
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * @param int[]|string[] $userIds
     *
     * @return UserIdentity[]
     */
    public function getIdentitiesByUserIds(array $userIds): array
    {
        return $this->whereIn('user_id', $userIds)->findAll();
    }

    /**
     * @param int|string $userId
     */
    public function getIdentityByType($userId, string $type): ?UserIdentity
    {
        return $this->where('user_id', $userId)
            ->where('type', $type)
            ->first();
    }

    /**
     * @param int|string $userId
     * @param string[]   $types
     *
     * @return UserIdentity[]
     */
    public function getIdentitiesByTypes($userId, array $types): array
    {
        return $this->where('user_id', $userId)
            ->whereIn('type', $types)
            ->findAll();
    }

    /**
     * @param int|string $userId
     */
    public function deleteIdentitiesByType($userId, string $type): void
    {
        $this->where('user_id', $userId)
            ->where('type', $type)
            ->delete();
    }

    /**
     * Delete any access tokens for the given raw token.
     *
     * @param int|string $userId
     */
    public function revokeAccessToken($userId, string $rawToken)
    {
        return $this->where('user_id', $userId)
            ->where('type', 'access_token')
            ->where('secret', hash('sha256', $rawToken))
            ->delete();
    }

    /**
     * Revokes all access tokens for this user.
     *
     * @param int|string $userId
     */
    public function revokeAllAccessTokens($userId)
    {
        return $this->where('user_id', $userId)
            ->where('type', 'access_token')
            ->delete();
    }

    public function fake(Generator &$faker): array
    {
        return [
            'user_id'      => fake(UserModel::class)->id,
            'type'         => 'email_password',
            'name'         => null,
            'secret'       => password_hash('secret', PASSWORD_DEFAULT),
            'secret2'      => null,
            'expires'      => null,
            'extra'        => null,
            'force_reset'  => false,
            'last_used_at' => null,
        ];
    }
}
