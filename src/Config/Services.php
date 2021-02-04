<?php

namespace Sparks\Shield\Config;

use Sparks\Shield\Auth;
use Sparks\Shield\Authentication\Authentication;
use Config\Services as BaseService;
use Sparks\Shield\Authentication\Passwords;

class Services extends BaseService
{
	/**
	 * The base auth class
	 *
	 * @param boolean $getShared
	 *
	 * @return \Sparks\Shield\Auth
	 */
	public static function auth(bool $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('auth');
		}

		$config = config('Auth');

		return new Auth(new Authentication($config));
	}

	/**
	 * Password utilities.
	 *
	 * @param boolean $getShared
	 *
	 * @return \Sparks\Shield\Authentication\Passwords|mixed
	 */
	public static function passwords(bool $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('passwords');
		}

		return new Passwords(config('Auth'));
	}
}
