<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Entities\User;
use DateTime;
use Faker\Generator;
use stdClass;

class RememberModel extends BaseModel
{
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

    protected function initialize(): void
    {
        parent::initialize();

        $this->table = $this->tables['remember_tokens'];
    }

    public function fake(Generator &$faker): stdClass
    {
        return (object) [
            'user_id'         => 1,
            'selector'        => 'selector',
            'hashedValidator' => 'validator',
            'expires'         => Time::parse('+1 day')->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Stores a remember-me token for the user.
     */
    public function rememberUser(User $user, string $selector, string $hashedValidator, string $expires): void
    {
        $expires = new DateTime($expires);

        $return = $this->insert([
            'user_id'         => $user->id,
            'selector'        => $selector,
            'hashedValidator' => $hashedValidator,
            'expires'         => $expires->format('Y-m-d H:i:s'),
        ]);

        $this->checkQueryReturn($return);
    }

    /**
     * Returns the remember-me token info for a given selector.
     */
    public function getRememberToken(string $selector): ?stdClass
    {
        return $this->where('selector', $selector)->get()->getRow(); // @phpstan-ignore-line
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
        $return = $this->where(['user_id' => $user->id])->delete();

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
