<?php

declare(strict_types=1);

namespace Tests\Authentication;

use CodeIgniter\Shield\Entities\AccessToken;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class AccessTokenTest extends DatabaseTestCase
{
    public function testCanNoScopes(): void
    {
        $token = new AccessToken();

        $this->assertFalse($token->can('foo'));
    }

    public function testCanWildcard(): void
    {
        $token = new AccessToken([
            'extra' => ['*'],
        ]);

        $this->assertTrue($token->can('foo'));
        $this->assertTrue($token->can('bar'));
    }

    public function testCanSuccess(): void
    {
        $token = new AccessToken([
            'extra' => ['foo'],
        ]);

        $this->assertTrue($token->can('foo'));
        $this->assertFalse($token->can('bar'));
    }

    public function testCantNoScopes(): void
    {
        $token = new AccessToken();

        $this->assertTrue($token->cant('foo'));
    }

    public function testCantWildcard(): void
    {
        $token = new AccessToken([
            'extra' => ['*'],
        ]);

        $this->assertFalse($token->cant('foo'));
        $this->assertFalse($token->cant('bar'));
    }

    public function testCantSuccess(): void
    {
        $token = new AccessToken([
            'extra' => ['foo'],
        ]);

        $this->assertFalse($token->cant('foo'));
        $this->assertTrue($token->cant('bar'));
    }
}
