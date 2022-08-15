<?php

declare(strict_types=1);

namespace Tests\Authentication\Filters;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Filters\ChainAuth;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\FakeUser;

/**
 * @internal
 */
final class ChainFilterTest extends AbstractFilterTest
{
    use DatabaseTestTrait;
    use FakeUser;

    protected string $alias     = 'chain';
    protected string $classname = ChainAuth::class;

    public function testFilterNotAuthorized(): void
    {
        $result = $this->call('get', 'protected-route');

        $result->assertRedirectTo('/login');

        $result = $this->get('open-route');
        $result->assertStatus(200);
        $result->assertSee('Open');
    }

    public function testFilterSuccessSeession(): void
    {
        $_SESSION['user']['id'] = $this->user->id;

        $result = $this->withSession(['user' => ['id' => $this->user->id]])
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($this->user->id, auth()->id());
        $this->assertSame($this->user->id, auth()->user()->id);
    }

    public function testFilterSuccessTokens(): void
    {
        $token = $this->user->generateAccessToken('foo');

        $result = $this->withHeaders(['Authorization' => 'Bearer ' . $token->raw_token])
            ->get('protected-route');

        $result->assertStatus(200);
        $result->assertSee('Protected');

        $this->assertSame($this->user->id, auth()->id());
        $this->assertSame($this->user->id, auth()->user()->id);

        // User should have the current token set.
        $this->assertInstanceOf(AccessToken::class, auth('tokens')->user()->currentAccessToken());
        $this->assertSame($token->id, auth('tokens')->user()->currentAccessToken()->id);
    }
}
