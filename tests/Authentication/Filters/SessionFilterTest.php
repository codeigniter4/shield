<?php

namespace Tests\Authentication\Filters;

use CodeIgniter\Config\Factories;
use CodeIgniter\Shield\Filters\SessionAuth;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class SessionFilterTest extends DatabaseTestCase
{
    use FeatureTestTrait;

    protected $namespace;

    protected function setUp(): void
    {
        Services::reset(true);

        parent::setUp();

        $_SESSION = [];

        // Register our filter
        $filterConfig                         = config('Filters');
        $filterConfig->aliases['sessionAuth'] = SessionAuth::class;
        Factories::injectMock('filters', 'filters', $filterConfig);

        // Add a test route that we can visit to trigger.
        $routes = service('routes');
        $routes->group('/', ['filter' => 'sessionAuth'], static function ($routes): void {
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
        $user                   = fake(UserModel::class);
        $_SESSION['user']['id'] = $user->id;

        $result = $this->withSession(['user' => ['id' => $user->id]])
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($user->id, auth('session')->id());
        $this->assertSame($user->id, auth('session')->user()->id);
        // Last Active should have been updated
        $this->assertNotEmpty(auth('session')->user()->last_active);
    }
}
