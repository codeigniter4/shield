<?php

declare(strict_types=1);

namespace Tests\Authentication\Filters;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Filters\PermissionFilter;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class PermissionFilterTest extends AbstractFilterTestCase
{
    use DatabaseTestTrait;

    protected string $alias       = 'permission';
    protected string $classname   = PermissionFilter::class;
    protected string $routeFilter = 'permission:admin.access';

    public function testFilterNotAuthorized(): void
    {
        $result = $this->call('get', 'protected-route');

        $result->assertRedirectTo('/login');

        $result = $this->get('open-route');
        $result->assertStatus(200);
        $result->assertSee('Open');
    }

    public function testFilterNotAuthorizedStoresRedirectToEntranceUrlIntoSession(): void
    {
        $result = $this->call('get', 'protected-route');

        $result->assertRedirectTo('/login');

        $this->assertNotEmpty(session()->getTempdata('beforeLoginUrl'));
        $this->assertSame(site_url('protected-route'), session()->getTempdata('beforeLoginUrl'));
    }

    public function testFilterSuccess(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class);
        $user->addPermission('admin.access');

        $result = $this
            ->actingAs($user)
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($user->id, auth('session')->id());
        $this->assertSame($user->id, auth('session')->user()->id);
    }

    public function testFilterIncorrectGroupNoPrevious(): void
    {
        /** @var User $user */
        $user = fake(UserModel::class);
        $user->addPermission('beta.access');

        $result = $this
            ->actingAs($user)
            ->get('protected-route');

        // Should redirect to home page since previous_url is not set
        $result->assertRedirectTo(site_url('/'));
        // Should have error message
        $result->assertSessionHas('error');
    }
}
