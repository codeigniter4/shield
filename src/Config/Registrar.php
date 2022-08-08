<?php

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Shield\Authentication\Passwords\ValidationRules as PasswordRules;
use CodeIgniter\Shield\Collectors\Auth;
use CodeIgniter\Shield\Filters\AuthRates;
use CodeIgniter\Shield\Filters\ChainAuth;
use CodeIgniter\Shield\Filters\SessionAuth;
use CodeIgniter\Shield\Filters\TokenAuth;

class Registrar
{
    /**
     * Registers the Shield filters.
     */
    public static function Filters(): array
    {
        return [
            'aliases' => [
                'session'    => SessionAuth::class,
                'tokens'     => TokenAuth::class,
                'chain'      => ChainAuth::class,
                'auth-rates' => AuthRates::class,
            ],
        ];
    }

    public static function Validation(): array
    {
        return [
            'ruleSets' => [
                PasswordRules::class,
            ],
        ];
    }

    public static function Toolbar(): array
    {
        return [
            'collectors' => [
                Auth::class,
            ],
        ];
    }
}
