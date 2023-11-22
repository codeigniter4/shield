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

use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthRoutesTest extends TestCase
{
    public function testRoutes(): void
    {
        $collection = single_service('routes');
        $auth       = service('auth');

        $auth->routes($collection);

        $routes = $collection->getRoutes('get');

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

        $routes = $collection->getRoutes('get');

        $this->assertArrayNotHasKey('login', $routes);
        $this->assertArrayHasKey('register', $routes);
        $this->assertArrayHasKey('login/magic-link', $routes);
        $this->assertArrayHasKey('logout', $routes);
        $this->assertArrayHasKey('auth/a/show', $routes);
    }
}
