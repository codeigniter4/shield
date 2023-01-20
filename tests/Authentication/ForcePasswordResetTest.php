<?php

declare(strict_types=1);

namespace Tests\Authentication;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\Fabricator;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ForcePasswordResetTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    protected $refresh = true;
    protected $namespace;
    private string $routeFilter = 'force-reset';

    protected function setUp(): void
    {
        parent::setUp();

        // Load our Auth routes in the collection
        $routeCollection = service('routes');
        auth()->routes($routeCollection);

        $routeCollection->get('profile', static function (): void {
            echo 'Profile';
        }, ['filter' => $this->routeFilter]);

        Services::injectMock('routes', $routeCollection);
    }

    public function testUserRequiresPasswordReset(): void
    {
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        /**
         * @phpstan-var UserIdentityModel
         */
        $identity = model(UserIdentityModel::class);
        $identity->set('force_reset', true);
        $identity->where('user_id', $user->id);
        $identity->update();

        $this->assertTrue($user->requiresPasswordReset());
    }

    public function testForcePasswordResetOnUser(): void
    {
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $user->forcePasswordReset();

        $this->assertTrue($user->requiresPasswordReset());
    }

    public function testUndoForcePasswordResetOnUser(): void
    {
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $user->forcePasswordReset();
        $user->undoForcePasswordReset();

        $this->assertFalse($user->requiresPasswordReset());
    }

    public function testRequiresPasswordResetRedirect(): void
    {
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $user->forcePasswordReset();

        $result = $this->actingAs($user)->get('profile');

        // This should redirect to the stated url in Auth redirects
        $result->assertStatus(302);
        $result->assertRedirectTo(config('Auth')->forcePasswordResetRedirect());
    }

    public function testForceGlobalPasswordReset(): void
    {
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $this->actingAs($user);

        /**
         * @phpstan-var Fabricator
         */
        $fabricator = new Fabricator(UserIdentityModel::class);
        $fabricator->create(100);

        /**
         * @phpstan-var UserIdentityModel
         */
        $identities = model(UserIdentityModel::class);

        $response = $identities->forceGlobalPasswordReset();
        $this->assertTrue($response);
        $this->assertFalse($user->requiresPasswordReset());
    }
}
