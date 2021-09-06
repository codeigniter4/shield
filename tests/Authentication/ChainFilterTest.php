<?php

namespace Test\Authentication;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Sparks\Shield\Entities\AccessToken;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Filters\ChainAuth;
use Sparks\Shield\Models\UserModel;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

class ChainFilterTest extends TestCase
{
	use DatabaseTestTrait;
	use FeatureTestTrait;
	use FakeUser;

	protected $namespace = 'Sparks\Shield';

	public function setUp(): void
	{
		\Config\Services::reset();

		parent::setUp();

		$_SESSION = [];

		// Register our filter
		$filterConfig                   = config('Filters');
		$filterConfig->aliases['chain'] = ChainAuth::class;
		Factories::injectMock('filters', 'filters', $filterConfig);

		// Add a test route that we can visit to trigger.
		$routes = service('routes');
		$routes->group('/', ['filter' => 'chain'], function ($routes) {
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

	public function testFilterSuccessSeession()
	{
		$_SESSION['logged_in'] = $this->user->id;

		$result = $this->withSession(['logged_in' => $this->user->id])
					   ->get( 'protected-route');

		$result->assertStatus(200);
		$result->assertSee('Protected');

		$this->assertEquals($this->user->id, auth()->id());
		$this->assertEquals($this->user->id, auth()->user()->id);
	}

	public function testFilterSuccessTokens()
	{
		$token = $this->user->generateAccessToken('foo');

		$result = $this->withHeaders(['Authorization' => 'Bearer ' . $token->raw_token])
					   ->get( 'protected-route');

		$result->assertStatus(200);
		$result->assertSee('Protected');

		$this->assertEquals($this->user->id, auth()->id());
		$this->assertEquals($this->user->id, auth()->user()->id);

		// User should have the current token set.
		$this->assertInstanceOf(AccessToken::class, auth('tokens')->user()->currentAccessToken());
		$this->assertEquals($token->id, auth('tokens')->user()->currentAccessToken()->id);
	}
}
