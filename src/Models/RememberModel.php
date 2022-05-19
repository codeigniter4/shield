<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Model;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use DateTime;
use stdClass;

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
     */
    public function rememberUser(User $user, string $selector, string $hashedValidator, string $expires): void
    {
        $expires = new DateTime($expires);

        $return = $this->insert([
            'user_id'         => $user->getAuthId(),
            'selector'        => $selector,
            'hashedValidator' => $hashedValidator,
            'expires'         => $expires->format('Y-m-d H:i:s'),
        ]);

        $this->checkQueryReturn($return);
    }

    private function checkQueryReturn(bool $return): void
    {
        if ($return === false) {
            $error   = $this->db->error();
            $message = 'Query error: ' . $error['code'] . ', '
                . $error['message'] . ', query: ' . $this->db->getLastQuery();

            throw new RuntimeException($message, $error['code']);
        }
    }

    /**
     * Returns the remember-me token info for a given selector.
     *
     * @return stdClass|null
     */
    public function getRememberToken(string $selector)
    {
        return $this->where('selector', $selector)
            ->get()
            ->getRow();
    }

    /**
     * Updates the validator for a given selector.
     */
    public function updateRememberValidator(stdClass $token): void
    {
        $return = $this->save($token);

        $this->checkQueryReturn($return);
    }

    /**
     * Removes all persistent login tokens (remember-me) for a single user
     * across all devices they may have logged in with.
     */
    public function purgeRememberTokens(User $user): void
    {
        $return = $this->where(['user_id' => $user->getAuthId()])->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * Purges the 'auth_remember_tokens' table of any records that are past
     * their expiration date already.
     */
    public function purgeOldRememberTokens(): void
    {
        $return = $this->where('expires <=', date('Y-m-d H:i:s'))
            ->delete();

        $this->checkQueryReturn($return);
    }
}
