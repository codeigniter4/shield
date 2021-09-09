<?php

namespace Sparks\Shield\Config;

use Config\Services as BaseService;
use Sparks\Shield\Auth;
use Sparks\Shield\Authentication\Authentication;
use Sparks\Shield\Authentication\Passwords;

class Services extends BaseService
{
    /**
     * The base auth class
     *
     * @return \Sparks\Shield\Auth
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
     * @return mixed|\Sparks\Shield\Authentication\Passwords
     */
    public static function passwords(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('passwords');
        }

        return new Passwords(config('Auth'));
    }
}
