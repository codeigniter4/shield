<?php

namespace Test\Authentication;

use Sparks\Shield\Authentication\Authentication;
use Sparks\Shield\Authentication\Handlers\Session;
use Sparks\Shield\Authentication\Handlers\AccessTokens;
use Sparks\Shield\Config\Auth;
use Sparks\Shield\Models\UserModel;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Services;
use Mockery as m;

class PipelineHandlerTest extends CIDatabaseTestCase
{
	/**
	 * @var \Sparks\Shield\Authentication\Handlers\Session
	 */
	protected $auth;

	protected $namespace = '\Sparks\Shield';

	protected $events;

	/**
	 * @var \Sparks\Shield\Authentication\Handlers\Session
	 */
	protected $sessionHandler;

	/**
	 * @var \Sparks\Shield\Authentication\Handlers\AccessTokens
	 */
	protected $tokenHandler;

	public function setUp(): void
	{
		parent::setUp();

		$this->sessionHandler = m::mock(Session::class);
		$this->tokenHandler   = m::mock(AccessTokens::class);

		$config     = new Auth();
		$auth       = new Authentication($config);
		$this->auth = $auth->factory('pipeline');
		$this->setPrivateProperty($this->auth, 'instances', [
			$this->sessionHandler,
			$this->tokenHandler,
		]);

		$this->events = new MockEvents();
		Services::injectMock('events', $this->events);

		$this->db->table('auth_identities')->truncate();
	}

	public function testLoggedInSession()
	{
		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('loggedIn')->once()->andReturn(true);
		$this->tokenHandler->shouldNotReceive('loggedIn');

		$this->assertTrue($this->auth->loggedIn());
	}

	public function testLoggedInTokens()
	{
		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('loggedIn');
		$this->tokenHandler->shouldReceive('loggedIn')->once()->andReturn(true);

		$this->assertTrue($this->auth->loggedIn());
	}

	public function testAttemptSession()
	{
		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('attempt')->andReturn(true);
		$this->tokenHandler->shouldNotReceive('attempt');

		$this->assertTrue($this->auth->attempt([]));
	}

	public function testAttemptTokens()
	{
		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('attempt')->andReturn(false);
		$this->tokenHandler->shouldReceive('attempt')->andReturn(true);

		$this->assertTrue($this->auth->attempt([]));
	}

	public function testCheckSession()
	{
		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('check')->andReturn(true);
		$this->tokenHandler->shouldNotReceive('check');

		$this->assertTrue($this->auth->check([]));
	}

	public function testCheckTokens()
	{
		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('check')->andReturn(false);
		$this->tokenHandler->shouldReceive('check')->andReturn(true);

		$this->assertTrue($this->auth->check([]));
	}

	public function testLoginSession()
	{
		$user = fake(UserModel::class);

		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('login')->andReturn(true);
		$this->tokenHandler->shouldNotReceive('login');

		$this->assertTrue($this->auth->login($user));
	}

	public function testLoginTokens()
	{
		$user = fake(UserModel::class);

		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('login')->andReturn(false);
		$this->tokenHandler->shouldReceive('login')->andReturn(true);

		$this->assertTrue($this->auth->login($user));
	}

	public function testLoginByIdSession()
	{
		$user = fake(UserModel::class);

		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('loginById')->andReturn(true);
		$this->tokenHandler->shouldNotReceive('loginById');

		$this->assertTrue($this->auth->loginById($user->id));
	}

	public function testLoginByIdTokens()
	{
		$user = fake(UserModel::class);

		// Session handler is first in the list so only it should be called.
		$this->sessionHandler->shouldReceive('loginById')->andReturn(false);
		$this->tokenHandler->shouldReceive('loginById')->andReturn(true);

		$this->assertTrue($this->auth->loginById($user->id));
	}
}
