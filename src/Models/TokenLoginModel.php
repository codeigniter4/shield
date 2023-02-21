<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use CodeIgniter\Shield\Entities\Login;
use Exception;
use Faker\Generator;

class TokenLoginModel extends Model
{
    use CheckQueryReturnTrait;

    protected $table          = 'auth_token_logins';
    protected $primaryKey     = 'id';
    protected $returnType     = Login::class;
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'ip_address',
        'user_agent',
        'identifier',
        'user_id',
        'date',
        'success',
    ];
    protected $useTimestamps   = false;
    protected $validationRules = [
        'ip_address' => 'required',
        'identifier' => 'required',
        'user_agent' => 'permit_empty|string',
        'user_id'    => 'permit_empty|integer',
        'date'       => 'required|valid_date',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * @param int|string|null $userId
     */
    public function recordLoginAttempt(
        string $identifier,
        bool $success,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        $userId = null
    ): void {
        $return = $this->insert([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'identifier' => $identifier,
            'user_id'    => $userId,
            'date'       => date('Y-m-d H:i:s'),
            'success'    => (int) $success,
        ]);

        $this->checkQueryReturn($return);
    }

    /**
     * Generate a fake login for testing
     *
     * @throws Exception
     */
    public function fake(Generator &$faker): Login
    {
        return new Login([
            'ip_address' => $faker->ipv4,
            'identifier' => 'token: ' . random_string('crypto', 64),
            'user_id'    => fake(UserModel::class)->id,
            'date'       => Time::parse('-1 day')->toDateTimeString(),
            'success'    => true,
        ]);
    }
}
