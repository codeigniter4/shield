<?php

declare(strict_types=1);

namespace Tests\Authentication\Filters;

use CodeIgniter\Shield\Filters\SessionAuth;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class SessionFilterTest extends AbstractFilterTest
{
    use DatabaseTestTrait;

    protected string $alias     = 'sessionAuth';
    protected string $classname = SessionAuth::class;

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

    public function testRecordActiveDate(): void
    {
        $user                   = fake(UserModel::class);
        $_SESSION['user']['id'] = $user->id;

        $this->withSession(['user' => ['id' => $user->id]])
            ->get('protected-route');

        // Last Active should be greater than 'updated_at' column
        $this->assertGreaterThan(auth('session')->user()->updated_at, auth('session')->user()->last_active);
    }

    public function testBlocksInactiveUsers(): void
    {
        $user = fake(UserModel::class, ['active' => false]);

        // Activation only required with email activation
        setting('Auth.actions', ['register' => null]);

        $result = $this->actingAs($user)
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        // Now require user activation and try again
        setting('Auth.actions', ['register' => '\CodeIgniter\Shield\Authentication\Actions\EmailActivator']);

        $result = $this->actingAs($user)
            ->get('protected-route');

        $result->assertRedirectTo('/login');
        // User should be logged out
        $this->assertNull(auth('session')->id());

        setting('Auth.actions', ['register' => null]);
    }
}
