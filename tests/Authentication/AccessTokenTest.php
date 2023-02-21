<?php

namespace Test\Authentication;

use CodeIgniter\Test\CIDatabaseTestCase;
use Sparks\Shield\Entities\AccessToken;

/**
 * @internal
 */
final class AccessTokenTest extends CIDatabaseTestCase
{
    protected $namespace = '\Sparks\Shield';

    public function testCanNoScopes()
    {
        $token = new AccessToken();

        $this->assertFalse($token->can('foo'));
    }

    public function testCanWildcard()
    {
        $token = new AccessToken([
            'extra' => ['*'],
        ]);

        $this->assertTrue($token->can('foo'));
        $this->assertTrue($token->can('bar'));
    }

    public function testCanSuccess()
    {
        $token = new AccessToken([
            'extra' => ['foo'],
        ]);

        $this->assertTrue($token->can('foo'));
        $this->assertFalse($token->can('bar'));
    }

    public function testCantNoScopes()
    {
        $token = new AccessToken();

        $this->assertTrue($token->cant('foo'));
    }

    public function testCantWildcard()
    {
        $token = new AccessToken([
            'extra' => ['*'],
        ]);

        $this->assertFalse($token->cant('foo'));
        $this->assertFalse($token->cant('bar'));
    }

    public function testCantSuccess()
    {
        $token = new AccessToken([
            'extra' => ['foo'],
        ]);

        $this->assertFalse($token->cant('foo'));
        $this->assertTrue($token->cant('bar'));
    }
}
