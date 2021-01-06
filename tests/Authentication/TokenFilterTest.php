<?php

namespace Test\Authentication;

use CodeIgniter\Config\Factories;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Filters\TokenAuth;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class TokenFilterTest extends CIDatabaseTestCase
{
	use FeatureTestTrait;

	protected $namespace = 'CodeIgniter\Shield';

	public function setUp(): void
	{
		\Config\Services::reset();

		parent::setUp();

		$_SESSION = [];

		// Register our filter
		$filterConfig                       = config('Filters');
		$filterConfig->aliases['tokenAuth'] = TokenAuth::class;
		Factories::injectMock('filters', 'filters', $filterConfig);

		// Add a test route that we can visit to trigger.
		$routes = service('routes');
		$routes->group('/', ['filter' => 'tokenAuth'], function ($routes) {
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

		$result->assertRedirect('/login');

		$result = $this->get('open-route');
		$result->assertStatus(200);
		$result->assertSee('Open');
	}

	public function testFilterSuccess()
	{
		$user  = fake(UserModel::class);
		$token = $user->generateAccessToken('foo');

		$result = $this->withHeaders(['Authorization' => 'Bearer ' . $token->raw_token])
					   ->get( 'protected-route');

		$result->assertStatus(200);
		$result->assertSee('Protected');

		$this->assertEquals($user->id, auth('tokens')->id());
		$this->assertEquals($user->id, auth('tokens')->user()->id);

		// User should have the current token set.
		$this->assertInstanceOf(AccessToken::class, auth('tokens')->user()->currentAccessToken());
		$this->assertEquals($token->id, auth('tokens')->user()->currentAccessToken()->id);
	}
}
