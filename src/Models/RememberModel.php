<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Model;
use DateTime;

class RememberModel extends Model
{
    protected $table          = 'auth_remember_tokens';
    protected $primaryKey     = 'id';
    protected $returnType     = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'selector',
        'hashedValidator',
        'user_id',
        'expires',
    ];
    protected $useTimestamps = true;

    /**
     * Stores a remember-me token for the user.
     *
     * @param int|string $userId
     *
     * @return mixed
     */
    public function rememberUser($userId, string $selector, string $validator, string $expires)
    {
        $expires = new DateTime($expires);

        return $this->insert([
            'user_id'         => $userId,
            'selector'        => $selector,
            'hashedValidator' => $validator,
            'expires'         => $expires->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Returns the remember-me token info for a given selector.
     *
     * @return mixed
     */
    public function getRememberToken(string $selector)
    {
        return $this->where('selector', $selector)
            ->get()
            ->getRow();
    }

    /**
     * Updates the validator for a given selector.
     *
     * @return mixed
     */
    public function updateRememberValidator(string $selector, string $validator)
    {
        return $this->where('selector', $selector)
            ->update(['hashedValidator' => hash('sha256', $validator)]);
    }

    /**
     * Removes all persistent login tokens (remember-me) for a single user
     * across all devices they may have logged in with.
     *
     * @param int|string|null $userId
     *
     * @return mixed
     */
    public function purgeRememberTokens($userId = null)
    {
        if (empty($userId)) {
            return;
        }

        return $this->where(['user_id' => $userId])->delete();
    }

    /**
     * Purges the 'auth_remember_tokens' table of any records that are past
     * their expiration date already.
     */
    public function purgeOldRememberTokens(): void
    {
        $this->where('expires <=', date('Y-m-d H:i:s'))
            ->delete();
    }
}
