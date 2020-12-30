<?php

namespace CodeIgniter\Shield\Authentication;

class AuthenticationException extends \Exception
{
	protected $code = 403;

	public static function forUnknownHandler(string $handler)
	{
		return new self(lang('Auth.unknownHandler', [$handler]));
	}

	public static function forUnknownUserProvider()
	{
		return new self(lang('Auth.unknownUserProvider'));
	}

	public static function forInvalidUser()
	{
		return new self(lang('Auth.invalidUser'));
	}
}
