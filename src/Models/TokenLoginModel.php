<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Entities\Login;
use Faker\Generator;

class TokenLoginModel extends LoginModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->table = $this->tables['token_logins'];
    }

    /**
     * Generate a fake login for testing
     */
    public function fake(Generator &$faker): Login
    {
        return new Login([
            'ip_address' => $faker->ipv4(),
            'identifier' => 'token: ' . random_string('crypto', 64),
            'user_id'    => fake(UserModel::class)->id,
            'date'       => Time::parse('-1 day')->format('Y-m-d H:i:s'),
            'success'    => true,
        ]);
    }
}
