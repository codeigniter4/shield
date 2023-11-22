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

namespace Tests\Authentication;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\DatabaseTestTrait;
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
        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        /** @var UserIdentityModel $identity */
        $identity = model(UserIdentityModel::class);
        $identity->set('force_reset', 1);
        $identity->where('user_id', $user->id);
        $identity->update();

        $this->assertTrue($user->requiresPasswordReset());
    }

    public function testForcePasswordResetOnUser(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $user->forcePasswordReset();

        $this->assertTrue($user->requiresPasswordReset());
    }

    public function testUndoForcePasswordResetOnUser(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $user->forcePasswordReset();
        $user->undoForcePasswordReset();

        $this->assertFalse($user->requiresPasswordReset());
    }

    public function testRequiresPasswordResetRedirect(): void
    {
        /** @var User $user */
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
        for ($i = 0; $i < 3; $i++) {
            /** @var User $user */
            $user = fake(UserModel::class);
            $user->createEmailIdentity([
                'email' => 'foo' . $i . '@example.com', 'password' => $i . 'secret123',
            ]);

            $users[$i] = $user;
        }

        /** @var UserIdentityModel $identities */
        $identities = model(UserIdentityModel::class);
        $identities->forceGlobalPasswordReset();

        for ($i = 0; $i < 3; $i++) {
            $this->assertTrue($users[$i]->requiresPasswordReset());
        }
    }
}
