<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Entities\Login;
use Exception;
use Faker\Generator;

class TokenLoginModel extends LoginModel
{
    protected $table = 'auth_token_logins';

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
