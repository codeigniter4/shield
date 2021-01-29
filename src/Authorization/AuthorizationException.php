<?php

namespace Sparks\Shield\Authentication;

class AuthorizationException extends \Exception
{
	protected $code = 401;
}
