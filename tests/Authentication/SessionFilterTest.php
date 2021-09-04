<?php

namespace Test\Authentication;

use CodeIgniter\Config\Factories;
use Sparks\Shield\Filters\SessionAuth;
use Sparks\Shield\Models\UserModel;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class SessionFilterTest extends CIDatabaseTestCase
{
	use FeatureTestTrait;

	protected $namespace = 'Sparks\Shield';

	public function setUp(): void
	{
		\Config\Services::reset();

		parent::setUp();

		$_SESSION = [];

		// Register our filter
		$filterConfig                         = config('Filters');
		$filterConfig->aliases['sessionAuth'] = SessionAuth::class;
		Factories::injectMock('filters', 'filters', $filterConfig);

		// Add a test route that we can visit to trigger.
		$routes = service('routes');
		$routes->group('/', ['filter' => 'sessionAuth'], function ($routes) {
			$routes->get('protected-route', function () {
				echo 'Protected';
			});
		});
		$routes->get('open-route', function () {
			echo 'Open';
		});
		$routes->get('login', 'AuthController::login', ['as' => 'login']);
		\Config\Services::injectMock('routes', $routes);
	}

	public function testFilterNotAuthorized()
	{
		$result = $this->call('get', 'protected-route');

		$result->assertRedirectTo('/login');

		$result = $this->get('open-route');
		$result->assertStatus(200);
		$result->assertSee('Open');
	}

	public function testFilterSuccess()
	{
		$user                  = fake(UserModel::class);
		$_SESSION['logged_in'] = $user->id;

		$result = $this->withSession(['logged_in' => $user->id])
					   ->get( 'protected-route');

		$result->assertStatus(200);
		$result->assertSee('Protected');

		$this->assertEquals($user->id, auth('session')->id());
		$this->assertEquals($user->id, auth('session')->user()->id);
	}
}
