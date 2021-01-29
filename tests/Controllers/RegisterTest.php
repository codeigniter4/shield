<?php

use CodeIgniter\Test\FeatureTestTrait;
use Sparks\Shield\Authentication\Passwords\ValidationRules;

class RegisterTest extends \CodeIgniter\Test\CIDatabaseTestCase
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

		// Ensure password validation rule is present
		$config = config('Validation');
		array_push($config->ruleSets, ValidationRules::class);
		CodeIgniter\Config\Config::injectMock('Validation', $config);
	}

	public function testRegisterDisplaysForm()
	{
		$result = $this->get('/register');

		$result->assertStatus(200);
		$result->assertSee(lang('Auth.register'));
	}

	public function testRegisterRedirectsIfNotAllowed()
	{
		$config                    = config('Auth');
		$config->allowRegistration = false;
		CodeIgniter\Config\Config::injectMock('Auth', $config);

		$result = $this->get('/register');

		$result->assertStatus(302);
		$result->assertRedirect();
	}

	public function testRegisterActionRedirectsIfNotAllowed()
	{
		$config                    = config('Auth');
		$config->allowRegistration = false;
		CodeIgniter\Config\Config::injectMock('Auth', $config);

		$result = $this->post('/register');

		$result->assertStatus(302);
		$result->assertRedirect();
	}

	public function testRegisterActionInvalidData()
	{
		$result = $this->post('/register');

		$result->assertStatus(302);
		$this->assertCount(4, session('errors'));
	}

	public function testRegisterActionSuccess()
	{
		$result = $this->post('/register', [
			'username'              => 'JohnDoe',
			'email'                 => 'john.doe@example.com',
			'password'              => 'secret things might happen here',
			'password_confirmation' => 'secret things might happen here',
		]);

		$result->assertStatus(302);
		$this->seeInDatabase('users', [
			'username' => 'JohnDoe',
			'email'    => 'john.doe@example.com',
		]);
	}
}
