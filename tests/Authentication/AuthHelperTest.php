<?php

namespace Test\Authentication;

use Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use Sparks\Shield\Authentication\AuthenticationException;

class AuthHelperTest extends CIUnitTestCase
{
	public function __construct()
	{
		parent::__construct();

		helper('auth');

		$this->setPrivateProperty(auth(), 'handler', null);
	}

	public function testAuthReturnsDefaultHandler()
	{
		$handlerName = config('Auth')->authenticators[config('Auth')->defaultAuthenticator];

		$this->assertInstanceOf($handlerName, auth()->getAuthenticator());
	}

	public function testAuthReturnsSpecifiedHandler()
	{
		$handlerName = config('Auth')->authenticators['tokens'];

		$this->assertInstanceOf($handlerName, auth('tokens')->getAuthenticator());
	}

	public function testAuthThrowsWithInvalidHandler()
	{
		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessage(lang('Auth.unknownHandler', ['foo']));

		auth('foo')->user();
	}
}
