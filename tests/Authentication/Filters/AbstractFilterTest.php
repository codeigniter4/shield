<?php

declare(strict_types=1);

namespace Tests\Authentication\Filters;

use CodeIgniter\Config\Factories;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\TestCase;

/**
 * @internal
 */
abstract class AbstractFilterTest extends TestCase
{
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $namespace;
    protected string $alias;
    protected string $classname;

    protected function setUp(): void
    {
        $_SESSION = [];

        Services::reset(true);
        helper('test');

        parent::setUp();

        // Register our filter
        $this->registerFilter();

        // Add a test route that we can visit to trigger.
        $this->addRoutes();
    }

    private function registerFilter(): void
    {
        $filterConfig = config('Filters');

        $filterConfig->aliases[$this->alias] = $this->classname;

        Factories::injectMock('filters', 'filters', $filterConfig);
    }

    private function addRoutes(): void
    {
        $routes = service('routes');

        $filterString = ! empty($this->routeFilter)
            ? $this->routeFilter
            : $this->alias;

        $routes->group(
            '/',
            ['filter' => $filterString],
            static function ($routes): void {
                $routes->get('protected-route', static function (): void {
                    echo 'Protected';
                });
            }
        );
        $routes->get('open-route', static function (): void {
            echo 'Open';
        });
        $routes->get('login', 'AuthController::login', ['as' => 'login']);
        $routes->get('protected-user-route', static function (): void {
            echo 'Protected';
        }, ['filter' => $this->alias . ':users-read']);

        Services::injectMock('routes', $routes);
    }
}
