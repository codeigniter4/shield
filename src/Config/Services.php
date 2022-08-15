<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\Passwords;
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
}
