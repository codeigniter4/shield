<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Config\Auth as AuthConfig;

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

        /** @var AuthConfig $config */
        $config = config('Auth');

        return new Auth($config);
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
