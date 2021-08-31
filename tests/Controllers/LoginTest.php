<?php

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\FeatureTestTrait;
use Sparks\Shield\Models\UserModel;

class LoginTest extends \CodeIgniter\Test\CIDatabaseTestCase
{
	use FeatureTestTrait;

	protected $namespace = '\Sparks\Shield';

	public function setUp(): void
	{
		parent::setUp();

		helper('auth');

		// Add auth routes
		$routes = service('routes');
		auth()->routes($routes);
		\Config\Services::injectMock('routes', $routes);
	}

	public function testLoginBadEmail()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity([
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		$result = $this->post('/login', [
			'email'    => 'fooled@example.com',
			'password' => 'secret123',
		]);

		$result->assertStatus(302);
		$result->assertRedirect();
		$this->assertEquals(site_url('/login'), $result->getRedirectUrl());

		// Login should have been recorded successfully
		$this->seeInDatabase('auth_logins', [
			'email'   => 'fooled@example.com',
			'user_id' => null,
			'success' => 0,
		]);

		$this->assertTrue(! empty(session('error')));
		$this->assertEquals(lang('Auth.badAttempt'), session('error'));
	}

	public function testLoginActionEmailSuccess()
	{
		$user = fake(UserModel::class);
		$user->createEmailIdentity([
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		$result = $this->post('/login', [
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		$result->assertStatus(302);
		$result->assertRedirect();
		$this->assertEquals(site_url(), $result->getRedirectUrl());

		// Login should have been recorded successfully
		$this->seeInDatabase('auth_logins', [
			'email'   => 'foo@example.com',
			'user_id' => $user->id,
			'success' => true,
		]);
		// Last Used date should have been set
		$identity = $user->getEmailIdentity();
		$this->assertCloseEnough($identity->last_used_at->getTimestamp(), \CodeIgniter\I18n\Time::now()->getTimestamp());

		// Session should have `logged_in` value with user's id
		$this->assertEquals($user->id, session('logged_in'));
	}

	public function testLogoutAction()
	{
		$user = fake(UserModel::class);

		// log them in
		session()->set('logged_in', $user->id);

		$result = $this->get('logout');

		$result->assertStatus(302);
		$this->assertEquals(site_url(config('Auth')->redirects['logout']), $result->getRedirectUrl());

		$this->assertNull(session('logged_in'));
	}

	public function testLoginRedirectsToActionIfDefined()
	{
		// Ensure our action is defined
		$config                   = config('Auth');
		$config->actions['login'] = \Sparks\Shield\Authentication\Actions\Email2FA::class;
		Factories::injectMock('config', 'Auth', $config);

		$user = fake(UserModel::class);
		$user->createEmailIdentity([
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		$result = $this->post('/login', [
			'email'    => 'foo@example.com',
			'password' => 'secret123',
		]);

		// Should have been redirected to the action's page.
		$result->assertStatus(302);
		$result->assertRedirect();
		$this->assertEquals(site_url('auth/a/show'), $result->getRedirectUrl());
	}
}
