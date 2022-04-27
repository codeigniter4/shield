<?php

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\Passwords;
use Config\Services as BaseService;

class Services extends BaseService
{
    /**
     * The base auth class
     *
     * @return \CodeIgniter\Shield\Auth
     */
    public static function auth(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('auth');
        }

        $config = config('Auth');

        return new Auth(new Authentication($config));
    }

    /**
     * Password utilities.
     *
     * @return \CodeIgniter\Shield\Authentication\Passwords|mixed
     */
    public static function passwords(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('passwords');
        }

        return new Passwords(config('Auth'));
    }
}
