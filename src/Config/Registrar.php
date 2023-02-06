<?php

namespace Sparks\Shield\Config;

use Sparks\Shield\Authentication\Passwords\ValidationRules as PasswordRules;
use Sparks\Shield\Filters\ChainAuth;
use Sparks\Shield\Filters\SessionAuth;
use Sparks\Shield\Filters\TokenAuth;

include_once __DIR__ . '/Constants.php';

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
        ];
    }
}
