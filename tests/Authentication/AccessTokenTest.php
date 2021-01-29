<?php

namespace Test\Authentication;

use Sparks\Shield\Models\AccessTokenModel;
use CodeIgniter\Test\CIDatabaseTestCase;

class AccessTokenTest extends CIDatabaseTestCase
{
	protected $namespace = '\Sparks\Shield';

	public function testCanNoScopes()
	{
		$token = fake(AccessTokenModel::class);

		$this->assertFalse($token->can('foo'));
	}

	public function testCanWildcard()
	{
		$token = fake(AccessTokenModel::class, ['scopes' => ['*']]);

		$this->assertTrue($token->can('foo'));
		$this->assertTrue($token->can('bar'));
	}

	public function testCanSuccess()
	{
		$token = fake(AccessTokenModel::class, ['scopes' => ['foo']]);

		$this->assertTrue($token->can('foo'));
		$this->assertFalse($token->can('bar'));
	}

	public function testCantNoScopes()
	{
		$token = fake(AccessTokenModel::class);

		$this->assertTrue($token->cant('foo'));
	}

	public function testCantWildcard()
	{
		$token = fake(AccessTokenModel::class, ['scopes' => ['*']]);

		$this->assertFalse($token->cant('foo'));
		$this->assertFalse($token->cant('bar'));
	}

	public function testCantSuccess()
	{
		$token = fake(AccessTokenModel::class, ['scopes' => ['foo']]);

		$this->assertFalse($token->cant('foo'));
		$this->assertTrue($token->cant('bar'));
	}
}
