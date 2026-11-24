<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Tests\Authentication\Filters;

use CodeIgniter\Config\Factories;
use CodeIgniter\Shield\Authentication\Authenticators\JWT;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Filters\JWTAuth;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Tests\DatabaseTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class JWTFilterTest extends DatabaseTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        Services::reset(true);

        parent::setUp();

        $_SESSION = [];

        // Add JWT Authenticator
        /** @var Auth $config */
        $config                        = config('Auth');
        $config->authenticators['jwt'] = JWT::class;

        // Register our filter
        $filterConfig                     = \config('Filters');
        $filterConfig->aliases['jwtAuth'] = JWTAuth::class;
        Factories::injectMock('filters', 'filters', $filterConfig);

        // Add a test route that we can visit to trigger.
        $routes = \service('routes');
        $routes->group('/', ['filter' => 'jwtAuth'], static function ($routes): void {
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
        $result = $this->get('protected-route');

        $result->assertStatus(401);

        $result = $this->get('open-route');

        $result->assertStatus(200);
        $result->assertSee('Open');
    }

    public function testFilterSuccess(): void
    {
        /** @var User $user */
        $user = \fake(UserModel::class);

        $generator = new JWTManager();
        $token     = $generator->generateToken($user);

        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($user->id, \auth('jwt')->id());
        $this->assertSame($user->id, \auth('jwt')->user()->id);
    }

    public function testFilterRejectsOnlyTheRevokedToken(): void
    {
        /** @var User $user */
        $user = \fake(UserModel::class);

        $generator  = new JWTManager();
        $firstToken = $generator->generateToken($user);
        $otherToken = $generator->generateToken($user);

        $this->withHeaders(['Authorization' => 'Bearer ' . $firstToken])
            ->get('protected-route')
            ->assertStatus(200);

        $generator->revokeToken($firstToken);

        $this->withHeaders(['Authorization' => 'Bearer ' . $firstToken])
            ->get('protected-route')
            ->assertStatus(401);

        $this->withHeaders(['Authorization' => 'Bearer ' . $otherToken])
            ->get('protected-route')
            ->assertStatus(200);

        $this->seeInDatabase('auth_token_logins', [
            'id_type'    => JWT::ID_TYPE_JWT,
            'identifier' => 'sha256:' . hash('sha256', $firstToken),
            'success'    => 0,
        ]);
        $this->dontSeeInDatabase('auth_token_logins', ['identifier' => $firstToken]);
    }
}
