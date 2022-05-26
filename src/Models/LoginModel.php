<?php

namespace Sparks\Shield\Models;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Exception;
use Faker\Generator;
use Sparks\Shield\Entities\Login;

class LoginModel extends Model
{
    protected $table          = 'auth_logins';
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
     *
     * @return BaseResult|false|int|object|string
     */
    public function recordLoginAttempt(
        string $identifier,
        bool $success,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        $userId = null
    ) {
        return $this->insert([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'identifier' => $identifier,
            'user_id'    => $userId,
            'date'       => date('Y-m-d H:i:s'),
            'success'    => (int) $success,
        ]);
    }

    /**
     * Generate a fake login for testing
     *
     * @throws Exception
     *
     * @return array
     */
    public function fake(Generator &$faker)
    {
        return [
            'ip_address' => $faker->ipv4,
            'identifier' => $faker->email,
            'user_id'    => null,
            'date'       => Time::parse('-1 day')->toDateTimeString(),
            'success'    => true,
        ];
    }
}
