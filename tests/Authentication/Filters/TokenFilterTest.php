<?php

declare(strict_types=1);

namespace Tests\Authentication\Filters;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Filters\TokenAuth;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class TokenFilterTest extends AbstractFilterTest
{
    use DatabaseTestTrait;

    protected string $alias     = 'tokenAuth';
    protected string $classname = TokenAuth::class;

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
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token->raw_token])
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($user->id, auth('tokens')->id());
        $this->assertSame($user->id, auth('tokens')->user()->id);

        // User should have the current token set.
        $this->assertInstanceOf(AccessToken::class, auth('tokens')->user()->currentAccessToken());
        $this->assertSame($token->id, auth('tokens')->user()->currentAccessToken()->id);
    }

    public function testRecordActiveDate(): void
    {
        /** @var User $user */
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $this->withHeaders(['Authorization' => 'Bearer ' . $token->raw_token])
            ->get('protected-route');

        // Last Active should be greater than 'updated_at' column
        $this->assertGreaterThan(auth('tokens')->user()->updated_at, auth('tokens')->user()->last_active);
    }

    public function testFiltersProtectsWithScopes(): void
    {
        /** @var User $user1 */
        $user1  = fake(UserModel::class);
        $token1 = $user1->generateAccessToken('foo', ['users-read']);
        /** @var User $user2 */
        $user2  = fake(UserModel::class);
        $token2 = $user2->generateAccessToken('foo', ['users-write']);

        // User 1 should be able to access the route
        $this->withHeaders(['Authorization' => 'Bearer ' . $token1->raw_token])
            ->get('protected-user-route');

        // Last Active should be greater than 'updated_at' column
        $this->assertGreaterThan(auth('tokens')->user()->updated_at, auth('tokens')->user()->last_active);

        // User 2 should NOT be able to access the route
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token2->raw_token])
            ->get('protected-user-route');

        $result->assertRedirectTo('/login');
    }
}
