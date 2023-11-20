<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Shield\Authentication\Passwords\ValidationRules as PasswordRules;
use CodeIgniter\Shield\Collectors\Auth;
use CodeIgniter\Shield\Filters\AuthRates;
use CodeIgniter\Shield\Filters\ChainAuth;
use CodeIgniter\Shield\Filters\ForcePasswordResetFilter;
use CodeIgniter\Shield\Filters\GroupFilter;
use CodeIgniter\Shield\Filters\HmacAuth;
use CodeIgniter\Shield\Filters\JWTAuth;
use CodeIgniter\Shield\Filters\PermissionFilter;
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
                'session'     => SessionAuth::class,
                'tokens'      => TokenAuth::class,
                'hmac'        => HmacAuth::class,
                'chain'       => ChainAuth::class,
                'auth-rates'  => AuthRates::class,
                'group'       => GroupFilter::class,
                'permission'  => PermissionFilter::class,
                'force-reset' => ForcePasswordResetFilter::class,
                'jwt'         => JWTAuth::class,
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

    public static function Generators(): array
    {
        return [
            'views' => [
                'shield:model' => 'CodeIgniter\Shield\Commands\Generators\Views\usermodel.tpl.php',
            ],
        ];
    }
}
