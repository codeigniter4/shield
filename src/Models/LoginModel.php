<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Database\BaseResult;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use CodeIgniter\Shield\Entities\Login;
use Exception;
use Faker\Generator;

class LoginModel extends Model
{
    protected $table          = 'auth_logins';
    protected $primaryKey     = 'id';
    protected $returnType     = Login::class;
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'ip_address',
        'user_agent',
        'email',
        'user_id',
        'date',
        'success',
    ];
    protected $useTimestamps   = false;
    protected $validationRules = [
        'ip_address' => 'required',
        'email'      => 'required',
        'user_agent' => 'permit_empty|string',
        'user_id'    => 'permit_empty|integer',
        'date'       => 'required|valid_date',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     *                          * @param bool        $success
     *
     * @return BaseResult|false|int|object|string
     */
    public function recordLoginAttempt(string $email, bool $success, ?string $ipAddress = null, ?string $userAgent = null, ?int $userID = null)
    {
        return $this->insert([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'email'      => $email,
            'user_id'    => $userID,
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
            'email'      => $faker->email,
            'user_id'    => null,
            'date'       => Time::parse('-1 day')->toDateTimeString(),
            'success'    => true,
        ];
    }
}
