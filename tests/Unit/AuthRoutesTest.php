<?php

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
