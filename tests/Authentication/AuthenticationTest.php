<?php

namespace Test\Authentication;

use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Config\Auth;
use Tests\Support\TestCase;
use CodeIgniter\Shield\Authentication\Handlers\Session;

class AuthenticationTest extends TestCase
{
	/**
	 * @var \CodeIgniter\Shield\Authentication\Authentication
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
