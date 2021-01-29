<?php

namespace Test\Authentication;

use Sparks\Shield\Authentication\Authentication;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Config\Auth;
use Tests\Support\TestCase;
use Sparks\Shield\Authentication\Handlers\Session;

class AuthenticationTest extends TestCase
{
	/**
	 * @var \Sparks\Shield\Authentication\Authentication
	 */
	protected $auth;

	public function setUp(): void
	{
		parent::setUp();

		$config     = new Auth();
		$this->auth = new Authentication($config);
	}

	public function testThrowsOnUnknownHandler()
	{
		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessage(lang('Auth.unknownHandler', ['foo']));

		$this->auth->factory('foo');
	}

	public function testFactoryLoadsDefault()
	{
		$shield1 = $this->auth->factory();
		$shield2 = $this->auth->factory('default');

		$this->assertInstanceOf(Session::class, $shield1);
		$this->assertSame($shield1, $shield2);
	}
}
