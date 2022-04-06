<?php

namespace Tests\Authentication;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Sparks\Shield\Entities\AccessToken;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Filters\ChainAuth;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ChainFilterTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use FakeUser;

    protected $namespace;

    protected function setUp(): void
    {
        Services::reset();

        parent::setUp();

        $_SESSION = [];

        // Register our filter
        $filterConfig                   = config('Filters');
        $filterConfig->aliases['chain'] = ChainAuth::class;
        Factories::injectMock('filters', 'filters', $filterConfig);

        // Add a test route that we can visit to trigger.
        $routes = service('routes');
        $routes->group('/', ['filter' => 'chain'], static function ($routes) {
            $routes->get('protected-route', static function () {
                echo 'Protected';
            });
        });
        $routes->get('open-route', static function () {
            echo 'Open';
        });
        $routes->get('login', 'AuthController::login', ['as' => 'login']);
        Services::injectMock('routes', $routes);
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
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($this->user->id, auth()->id());
        $this->assertSame($this->user->id, auth()->user()->id);
    }

    public function testFilterSuccessTokens()
    {
        $token = $this->user->generateAccessToken('foo');

        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token->raw_token])
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($this->user->id, auth()->id());
        $this->assertSame($this->user->id, auth()->user()->id);

        // User should have the current token set.
        $this->assertInstanceOf(AccessToken::class, auth('tokens')->user()->currentAccessToken());
        $this->assertSame($token->id, auth('tokens')->user()->currentAccessToken()->id);
    }
}
