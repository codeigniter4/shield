<?php

namespace Test\Authentication;

use Sparks\Shield\Authentication\Authentication;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Config\Auth;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Models\UserModel;
use Sparks\Shield\Result;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\Mock\MockEvents;
use Config\Services;
use Sparks\Shield\Models\RememberModel;

class SessionHandlerTest extends CIDatabaseTestCase
{
	/**
	 * @var \Sparks\Shield\Authentication\Handlers\Session
	 */
	protected $auth;

	protected $namespace = '\Sparks\Shield';

	protected $events;

	public function setUp(): void
	{
		parent::setUp();

		$config = new Auth();
		$auth   = new Authentication($config);
		$auth->setProvider(model(UserModel::class));
		$this->auth = $auth->factory('session');

		$this->events = new MockEvents();
		Services::injectMock('events', $this->events);

		$this->db->table('auth_identities')->truncate();
	}

	public function testLoggedIn()
	{
		$this->assertFalse($this->auth->loggedIn());

		$user                  = fake(UserModel::class);
		$_SESSION['logged_in'] = $user->id;

		$this->assertTrue($this->auth->loggedIn());

		$authUser = $this->auth->getUser();
		$this->assertInstanceOf(User::class, $authUser);
		$this->assertEquals($user->id, $authUser->id);
	}

	public function testLoginNoRemember()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

		$this->assertTrue($this->auth->login($user));

		$this->assertEquals($user->id, $_SESSION['logged_in']);

		// Login attempt should have been recorded
		$this->seeInDatabase('auth_logins', [
			'user_id' => $user->id,
			'email'   => $user->email,
			'success' => true,
		]);

		$this->dontSeeInDatabase('auth_remember_tokens', [
			'user_id' => $user->id,
		]);
	}

	public function testLoginWithRemember()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

		$this->assertTrue($this->auth->remember()->login($user));

		$this->assertEquals($user->id, $_SESSION['logged_in']);

		// Login attempt should have been recorded
		$this->seeInDatabase('auth_logins', [
			'user_id' => $user->id,
			'email'   => $user->email,
			'success' => true,
		]);

		$this->seeInDatabase('auth_remember_tokens', [
			'user_id' => $user->id,
		]);

		// Cookie should have been set
		$response = service('response');
		$this->assertNotNull($response->getCookie('remember'));
	}

	public function testLogout()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);
		$this->auth->remember()->login($user);

		$this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);

		$this->auth->logout();

		$this->assertFalse(isset($_SESSION['logged_in']));
		$this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
	}

	public function testLoginByIdBadUser()
	{
		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessage(lang('Auth.invalidUser'));

		$this->auth->loginById(123);
	}

	public function testLoginById()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

		$this->auth->loginById($user->id);

		$this->assertEquals($user->id, $_SESSION['logged_in']);

		$this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
	}

	public function testLoginByIdRemember()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);

		$this->auth->remember()->loginById($user->id);

		$this->assertEquals($user->id, $_SESSION['logged_in']);

		$this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
	}

	public function testForgetCurrentUser()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret']);
		$this->auth->remember()->loginById($user->id);
		$this->assertEquals($user->id, $_SESSION['logged_in']);

		$this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);

		$this->auth->forget();

		$this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
	}

	public function testForgetAnotherUser()
	{
		$user = fake(UserModel::class);
		fake(RememberModel::class, ['user_id' => $user->id]);

		$this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);

		$this->auth->forget($user->id);

		$this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
	}

	public function testCheckNoPassword()
	{
		$result = $this->auth->check([
			'email' => 'johnsmith@example.com',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.badAttempt'), $result->reason());
	}

	public function testCheckCannotFindUser()
	{
		$result = $this->auth->check([
			'email'    => 'johnsmith@example.com',
			'password' => 'secret',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.badAttempt'), $result->reason());
	}

	public function testCheckBadPassword()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity([
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		$result = $this->auth->check([
			'email'    => $user->email,
			'password' => 'secret',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.invalidPassword'), $result->reason());
	}

	public function testCheckSuccess()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity([
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		$result = $this->auth->check([
			'email'    => $user->email,
			'password' => 'secret123',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertTrue($result->isOK());

		$foundUser = $result->extraInfo();
		$this->assertEquals($user->id, $foundUser->id);
	}

	public function testAttemptCannotFindUser()
	{
		$result = $this->auth->attempt([
			'email'    => 'johnsmith@example.com',
			'password' => 'secret',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.badAttempt'), $result->reason());

		// A login attempt should have always been recorded
		$this->seeInDatabase('auth_logins', [
			'email'   => 'johnsmith@example.com',
			'success' => 0,
		]);
	}

	public function testAttemptSuccess()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity([
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		$this->assertFalse(isset($_SESSION['logged_in']));

		$result = $this->auth->attempt([
			'email'    => $user->email,
			'password' => 'secret123',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertTrue($result->isOK());

		$foundUser = $result->extraInfo();
		$this->assertEquals($user->id, $foundUser->id);

		$this->assertTrue(isset($_SESSION['logged_in']));
		$this->assertEquals($user->id, $_SESSION['logged_in']);

		// A login attempt should have been recorded
		$this->seeInDatabase('auth_logins', [
			'email'   => $user->email,
			'success' => 1,
		]);
	}

	public function testAttemptCaseInsensitive()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity([
			'email'    => 'FOO@example.com',
			'password' => 'secret123',
		]);

		$this->assertFalse(isset($_SESSION['logged_in']));

		$result = $this->auth->attempt([
			'email'    => 'foo@example.COM',
			'password' => 'secret123',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertTrue($result->isOK());

		$foundUser = $result->extraInfo();
		$this->assertEquals($user->id, $foundUser->id);

		$this->assertTrue(isset($_SESSION['logged_in']));
		$this->assertEquals($user->id, $_SESSION['logged_in']);

		// A login attempt should have been recorded
		$this->seeInDatabase('auth_logins', [
			'email'   => $user->email,
			'success' => 1,
		]);
	}

	public function testAttemptUsernameOnly()
	{
		$user = fake(UserModel::class, ['username' => 'foorog']);
		$user->createEmailIdentity([
			'email'    => 'FOO@example.com',
			'password' => 'secret123',
		]);

		$this->assertFalse(isset($_SESSION['logged_in']));

		$result = $this->auth->attempt([
			'username' => 'fooROG',
			'password' => 'secret123',
		]);

		$this->assertInstanceOf(Result::class, $result);
		$this->assertTrue($result->isOK());

		$foundUser = $result->extraInfo();
		$this->assertEquals($user->id, $foundUser->id);

		$this->assertTrue(isset($_SESSION['logged_in']));
		$this->assertEquals($user->id, $_SESSION['logged_in']);

		// A login attempt should have been recorded
		$this->seeInDatabase('auth_logins', [
			'email'   => $user->email,
			'success' => 1,
		]);
	}
}
