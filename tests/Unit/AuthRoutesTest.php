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

namespace Tests\Unit;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Auth;
use InvalidArgumentException;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthRoutesTest extends TestCase
{
    public function testRoutes(): void
    {
        /** @var RouteCollection $collection */
        $collection = single_service('routes');
        /** @var Auth $auth */
        $auth = service('auth');

        $auth->routes($collection);

        if (version_compare(CodeIgniter::CI_VERSION, '4.5') >= 0) {
            $routes = $collection->getRoutes('GET');
        } else {
            $routes = $collection->getRoutes('get');
        }

        $this->assertArrayHasKey('register', $routes);
        $this->assertArrayHasKey('login', $routes);
        $this->assertArrayHasKey('login/magic-link', $routes);
        $this->assertArrayHasKey('logout', $routes);
        $this->assertArrayHasKey('auth/a/show', $routes);
    }

    public function testRoutesExcept(): void
    {
        $collection = single_service('routes');
        $auth       = service('auth');

        $auth->routes($collection, ['except' => ['login']]);

        if (version_compare(CodeIgniter::CI_VERSION, '4.5') >= 0) {
            $routes = $collection->getRoutes('GET');
        } else {
            $routes = $collection->getRoutes('get');
        }

        $this->assertArrayNotHasKey('login', $routes);
        $this->assertArrayHasKey('register', $routes);
        $this->assertArrayHasKey('login/magic-link', $routes);
        $this->assertArrayHasKey('logout', $routes);
        $this->assertArrayHasKey('auth/a/show', $routes);
    }

    public function testRoutesOnly(): void
    {
        $collection = single_service('routes');
        $auth       = service('auth');

        $auth->routes($collection, ['only' => ['login']]);

        if (version_compare(CodeIgniter::CI_VERSION, '4.5') >= 0) {
            $routes = $collection->getRoutes('GET');
        } else {
            $routes = $collection->getRoutes('get');
        }

        $this->assertArrayHasKey('login', $routes);
        $this->assertArrayNotHasKey('register', $routes);
        $this->assertArrayNotHasKey('login/magic-link', $routes);
        $this->assertArrayNotHasKey('logout', $routes);
        $this->assertArrayNotHasKey('auth/a/show', $routes);
    }

    public function testRoutesUseOnlyAndExceptOptionsSimultaneous(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $collection = single_service('routes');
        $auth       = service('auth');

        $auth->routes($collection, ['only' => ['login'], 'except' => ['register']]);
    }

    public function testRoutesCustomNamespace(): void
    {
        $collection = single_service('routes');
        $auth       = service('auth');

        $auth->routes($collection, ['namespace' => 'Auth']);

        if (version_compare(CodeIgniter::CI_VERSION, '4.5') >= 0) {
            $routes = $collection->getRoutes('GET');
        } else {
            $routes = $collection->getRoutes('get');
        }

        $this->assertSame('\Auth\RegisterController::registerView', $routes['register']);
    }

    public function testRoutesCustomPrefix(): void
    {
        $collection = single_service('routes');
        $auth       = service('auth');

        $auth->routes($collection, ['group' => 'auth']);

        if (version_compare(CodeIgniter::CI_VERSION, '4.5') >= 0) {
            $routes = $collection->getRoutes('GET');
        } else {
            $routes = $collection->getRoutes('get');
        }

        $this->assertArrayHasKey('auth/register', $routes);
        $this->assertArrayHasKey('auth/login', $routes);
        $this->assertArrayHasKey('auth/login/magic-link', $routes);
        $this->assertArrayHasKey('auth/logout', $routes);
        $this->assertArrayHasKey('auth/auth/a/show', $routes);
    }
}
