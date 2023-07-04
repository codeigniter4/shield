<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Config\Auth as AuthConfig;
use Config\Services as BaseService;

class Services extends BaseService
{
    /**
     * The base auth class
     */
    public static function auth(bool $getShared = true): Auth
    {
        if ($getShared) {
            return self::getSharedInstance('auth');
        }

        $config = config(AuthConfig::class);

        return new Auth(new Authentication($config));
    }

    /**
     * Password utilities.
     */
    public static function passwords(bool $getShared = true): Passwords
    {
        if ($getShared) {
            return self::getSharedInstance('passwords');
        }

        return new Passwords(config(AuthConfig::class));
    }

    /**
     * JWT Manager.
     */
    public static function jwtmanager(bool $getShared = true): JWTManager
    {
        if ($getShared) {
            return self::getSharedInstance('jwtmanager');
        }

        return new JWTManager();
    }
}
