<?php

use CodeIgniter\Test\FeatureTestTrait;
use Sparks\Shield\Authentication\Passwords\ValidationRules;

class ActionsTest extends \CodeIgniter\Test\CIDatabaseTestCase
{
	use FeatureTestTrait;

	protected $namespace = '\Sparks\Shield';

	public function setUp(): void
	{
		parent::setUp();

		helper('auth');

		// Ensure our actions are registered with the system
		$config                   = config('Auth');
		$config->actions['login'] = \Sparks\Shield\Authentication\Actions\Email2FA::class;
		\CodeIgniter\Config\Factories::injectMock('config', 'Auth', $config);

		// Add auth routes
		$routes = service('routes');
		auth()->routes($routes);
		\Config\Services::injectMock('routes', $routes);

		$_SESSION = [];
	}

	public function testActionShowNoneAvailable()
	{
		$this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);

		$result = $this->withSession([])->get('/auth/a/show');

		// Nothing found, it should die gracefully.
		$result->assertStatus(404);
	}

	public function testEmail2FAShow()
	{
		$result = $this->withSession([
			'auth_action' => \Sparks\Shield\Authentication\Actions\Email2FA::class,
		])->get('/auth/a/show');

		$result->assertStatus(200);
	}

}
