<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Authentication\Passwords;

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

        $config = config('Auth');

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

        return new Passwords(config('Auth'));
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
