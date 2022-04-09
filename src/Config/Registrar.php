<?php

namespace Sparks\Shield\Config;

use Sparks\Shield\Authentication\Passwords\ValidationRules as PasswordRules;
use Sparks\Shield\Filters\ChainAuth;
use Sparks\Shield\Filters\SessionAuth;
use Sparks\Shield\Filters\TokenAuth;

class Registrar
{
    /**
     * Registers the Shield filters.
     */
    public static function Filters()
    {
        return [
            'aliases' => [
                'session' => SessionAuth::class,
                'tokens'  => TokenAuth::class,
                'chain'   => ChainAuth::class,
            ],
        ];
    }

    public static function Validation()
    {
        return [
            'ruleSets' => [
                PasswordRules::class,
            ],
            'users' => [
                'email'    => 'required|valid_email|unique_email[{id}]',
                'username' => 'required|string|is_unique[users.username,id,{id}]',
            ],
        ];
    }
}
