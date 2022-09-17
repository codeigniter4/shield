<?php

declare(strict_types=1);

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\Passwords\ValidationRules;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\FilterTestTrait;
use Config\Services;
use Tests\Support\DatabaseTestCase;
use Tests\Support\FakeUser;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Test\Mock\MockResourceController;
use Config\App;

/**
 * @internal
 */
final class StrongPasswordValidationTest extends DatabaseTestCase
{
    use FilterTestTrait;
    use FeatureTestTrait;
    use FakeUser;

    /**
     * @var RouteCollection
     */
    protected $routes;

    protected $namespace;

    protected function setUp(): void
    {
        Services::reset(true);

        parent::setUp();
        
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        // Inject mock router.
        $this->routes = Services::routes();
        auth()->routes($this->routes);
        $this->routes->post('api/users/signup', '\Tests\Controllers\Api\Users::signup');
        Services::injectMock('routes', $this->routes);

        // Ensure password validation rule is present
        $config             = config('Validation');
        $config->ruleSets[] = ValidationRules::class;
        $config->ruleSets   = array_reverse($config->ruleSets);
        Factories::injectMock('config', 'Validation', $config);
    }

    public function testWithoutUsingTokenBeforeFilter(): void
    {
        $result = $this->withSession()->post('/api/users/signup', [
            'email'            => 'foo@example.com',
            'username'         => 'foo',
            'password'         => 'foo#1234',
            'password_confirm' => 'foo#1234',
        ]);

        $expected = [
            'errors' => [
                'password' => "Passwords cannot contain re-hashed personal information.",
            ],
        ];

        $formatter = Services::format()->getFormatter('application/json');

        $this->assertSame($formatter->format($expected), $result->getJSON());
    }

    public function testUsingTokenBeforeFilter(): void
    {
        $filterConfig          = config('Filters');
        $filterConfig->filters = array_merge($filterConfig->filters, [
            'tokens' => [
                'before' => [
                    'api/users/signup', 
                ],
            ],
        ]);
        
        Factories::injectMock('config', 'Filters', $filterConfig);

        $this->assertHasFilters('api/users/signup', 'before');

        /** @var User $user */
        $user   = fake(UserModel::class);
        $token1 = $user->generateAccessToken('foo');
        $bearer = 'Bearer ' . $token1->raw_token;

        $result = $this
            ->withRoutes([
                ['post', 'api/users/signup', '\Tests\Controllers\Api\Users::signup'],
            ])
            ->withHeaders([
                'Authorization' => $bearer,
            ])
            ->post('api/users/signup', [
                'email'            => 'foo@example.com',
                'username'         => 'foo',
                'password'         => 'foo#1234',
                'password_confirm' => 'foo#1234',
            ]);

        $caller = $this->getFilterCaller('tokens', 'before');
        $caller();

        $expected = [
            'errors' => [
                'password' => "Passwords cannot contain re-hashed personal information.",
            ],
        ];

        $formatter = Services::format()->getFormatter('application/json');

        $this->assertSame($formatter->format($expected), $result->getJSON());
    }
}
