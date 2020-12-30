<?php

namespace CodeIgniter\Shield\Authentication;

class AuthorizationException extends \Exception
{
	protected $code = 401;
}
