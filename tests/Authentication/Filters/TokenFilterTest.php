<?php

namespace Tests\Authentication\Filters;

use CodeIgniter\Config\Factories;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Filters\TokenAuth;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class TokenFilterTest extends DatabaseTestCase
{
    use FeatureTestTrait;

    protected $namespace;

    protected function setUp(): void
    {
        $_SESSION = [];

        Services::reset(true);

        parent::setUp();

        // Register our filter
        $this->registerFilter();

        // Add a test route that we can visit to trigger.
        $this->addRoutes();
    }

    private function registerFilter(): void
    {
        $filterConfig = config('Filters');

        $filterConfig->aliases['tokenAuth'] = TokenAuth::class;

        Factories::injectMock('filters', 'filters', $filterConfig);
    }

    private function addRoutes(): void
    {
        $routes = service('routes');

        $routes->group('/', ['filter' => 'tokenAuth'], static function ($routes): void {
            $routes->get('protected-route', static function (): void {
                echo 'Protected';
            });
        });
        $routes->get('open-route', static function (): void {
            echo 'Open';
        });
        $routes->get('login', 'AuthController::login', ['as' => 'login']);

        Services::injectMock('routes', $routes);
    }

    public function testFilterNotAuthorized(): void
    {
        $result = $this->call('get', 'protected-route');

        $result->assertRedirectTo('/login');

        $result = $this->get('open-route');
        $result->assertStatus(200);
        $result->assertSee('Open');
    }

    public function testFilterSuccess(): void
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token->raw_token])
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($user->id, auth('tokens')->id());
        $this->assertSame($user->id, auth('tokens')->user()->id);

        // User should have the current token set.
        $this->assertInstanceOf(AccessToken::class, auth('tokens')->user()->currentAccessToken());
        $this->assertSame($token->id, auth('tokens')->user()->currentAccessToken()->id);
    }
}
