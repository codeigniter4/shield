<?php

namespace Tests\Authentication;

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class HasAccessTokensTest extends DatabaseTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = fake(UserModel::class);
        $this->db->table('auth_identities')->truncate();
    }

    public function testGenerateToken()
    {
        $token = $this->user->generateAccessToken('foo');

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertSame('foo', $token->name);
        $this->assertNull($token->expires);

        // When newly created, should have the raw, plain-text token as well
        // so it can be shown to users.
        $this->assertNotNull($token->raw_token);
        $this->assertSame($token->secret, hash('sha256', $token->raw_token));

        // All scopes are assigned by default via wildcard
        $this->assertSame(['*'], $token->scopes);
    }

    public function testAccessTokens()
    {
        // Should return empty array when none exist
        $this->assertSame([], $this->user->accessTokens());

        // Give the user a couple of access tokens
        $token1 = fake(
            UserIdentityModel::class,
            ['user_id' => $this->user->id, 'type' => 'access_token', 'secret' => 'secretToken1']
        );
        $token2 = fake(
            UserIdentityModel::class,
            ['user_id' => $this->user->id, 'type' => 'access_token', 'secret' => 'secretToken2']
        );

        /** @var AccessToken[] $tokens */
        $tokens = $this->user->accessTokens();

        $this->assertCount(2, $tokens);
        $this->assertSame($token1->id, $tokens[0]->id);
        $this->assertSame($token2->id, $tokens[1]->id);
    }

    public function testGetAccessToken()
    {
        // Should return null when not found
        $this->assertNull($this->user->getAccessToken('foo'));

        $token = $this->user->generateAccessToken('foo');

        $found = $this->user->getAccessToken($token->raw_token);

        $this->assertInstanceOf(AccessToken::class, $found);
        $this->assertSame($token->id, $found->id);
    }

    public function testGetAccessTokenById()
    {
        // Should return null when not found
        $this->assertNull($this->user->getAccessTokenById(123));

        $token = $this->user->generateAccessToken('foo');
        $found = $this->user->getAccessTokenById($token->id);

        $this->assertInstanceOf(AccessToken::class, $found);
        $this->assertSame($token->id, $found->id);
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
        $this->user->generateAccessToken('foo');
        $this->user->generateAccessToken('foo');

        $this->assertCount(2, $this->user->accessTokens());

        $this->user->revokeAllAccessTokens();

        $this->assertCount(0, $this->user->accessTokens());
    }

    public function testTokenCanNoTokenSet()
    {
        $this->assertFalse($this->user->tokenCan('foo'));
    }

    public function testTokenCanBasics()
    {
        $token = $this->user->generateAccessToken('foo', ['foo:bar']);
        $this->user->setAccessToken($token);

        $this->assertTrue($this->user->tokenCan('foo:bar'));
        $this->assertFalse($this->user->tokenCan('foo:baz'));
    }

    public function testTokenCantNoTokenSet()
    {
        $this->assertTrue($this->user->tokenCant('foo'));
    }

    public function testTokenCant()
    {
        $token = $this->user->generateAccessToken('foo', ['foo:bar']);
        $this->user->setAccessToken($token);

        $this->assertFalse($this->user->tokenCant('foo:bar'));
        $this->assertTrue($this->user->tokenCant('foo:baz'));
    }
}
