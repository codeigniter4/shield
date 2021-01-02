<?php

namespace Test\Authentication;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Models\AccessTokenModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\CIDatabaseTestCase;

class HasAccessTokensTest extends CIDatabaseTestCase
{
	protected $namespace = '\CodeIgniter\Shield';

	/**
	 * @var \CodeIgniter\Shield\Entities\User
	 */
	protected $user;

	public function setUp(): void
	{
		parent::setUp();

		$this->user = fake(UserModel::class);
	}

	public function testGenerateToken()
	{
		$token = $this->user->generateAccessToken('foo');

		$this->assertInstanceOf(AccessToken::class, $token);
		$this->assertEquals('foo', $token->name);
		$this->assertNull($token->expires);

		// When newly created, should have the raw, plain-text token as well
		// so it can be shown to users.
		$this->assertNotNull($token->raw_token);
		$this->assertEquals($token->token, hash('sha256', $token->raw_token));

		// All scopes are assigned by default via wildcard
		$this->assertEquals(['*'], $token->scopes);
	}

	public function testAccessTokens()
	{
		// Should return empty array when none exist
		$this->assertEquals([], $this->user->accessTokens());

		// Give the user a couple of access tokens
		$token1 = fake(AccessTokenModel::class, ['user_id' => $this->user->id]);
		$token2 = fake(AccessTokenModel::class, ['user_id' => $this->user->id]);

		$tokens = $this->user->accessTokens();
		$this->assertCount(2, $tokens);
		$this->assertEquals($token1->id, $tokens[0]->id);
		$this->assertEquals($token2->id, $tokens[1]->id);
	}

	public function testGetAccessToken()
	{
		// Should return null when not found
		$this->assertNull($this->user->getAccessToken('foo'));

		$token = $this->user->generateAccessToken('foo');

		$found = $this->user->getAccessToken($token->raw_token);

		$this->assertInstanceOf(AccessToken::class, $found);
		$this->assertEquals($token->id, $found->id);
	}

	public function testGetAccessTokenById()
	{
		// Should return null when not found
		$this->assertNull($this->user->getAccessTokenById(123));

		$token = $this->user->generateAccessToken('foo');
		$found = $this->user->getAccessTokenById($token->id);

		$this->assertInstanceOf(AccessToken::class, $found);
		$this->assertEquals($token->id, $found->id);
	}

	public function testRevokeAccessToken()
	{
		$token = $this->user->generateAccessToken('foo');

		$this->assertCount(1, $this->user->accessTokens());

		$this->user->revokeAccessToken($token->raw_token);

		$this->assertCount(0, $this->user->accessTokens());
	}

	public function testRevokeAllAccessTokens()
	{
		$token1 = $this->user->generateAccessToken('foo');
		$token2 = $this->user->generateAccessToken('foo');

		$this->assertCount(2, $this->user->accessTokens());

		$this->user->revokeAllAccessTokens();

		$this->assertCount(0, $this->user->accessTokens());
	}
}
