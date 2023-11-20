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

use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class HasHmacTokensTest extends DatabaseTestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = fake(UserModel::class);
        $this->db->table($this->tables['identities'])->truncate();
    }

    public function testGenerateHmacToken(): void
    {
        $token = $this->user->generateHmacToken('foo');

        $this->assertSame('foo', $token->name);
        $this->assertNull($token->expires);

        $this->assertIsString($token->secret);
        $this->assertIsString($token->secret2);

        // All scopes are assigned by default via wildcard
        $this->assertSame(['*'], $token->scopes);
    }

    public function testHmacTokens(): void
    {
        // Should return empty array when none exist
        $this->assertSame([], $this->user->accessTokens());

        // Give the user a couple of access tokens
        $token1 = fake(
            UserIdentityModel::class,
            ['user_id' => $this->user->id, 'type' => 'hmac_sha256', 'secret' => 'key1', 'secret2' => 'secretKey1']
        );

        $token2 = fake(
            UserIdentityModel::class,
            ['user_id' => $this->user->id, 'type' => 'hmac_sha256', 'secret' => 'key2', 'secret2' => 'secretKey2']
        );

        $tokens = $this->user->hmacTokens();

        $this->assertCount(2, $tokens);
        $this->assertSame($token1->id, $tokens[0]->id);
        $this->assertSame($token1->secret, $tokens[0]->secret); // Key
        $this->assertSame($token1->secret2, $tokens[0]->secret2); // Secret Key
        $this->assertSame($token2->id, $tokens[1]->id);
        $this->assertSame($token2->secret, $tokens[1]->secret);
        $this->assertSame($token2->secret2, $tokens[1]->secret2);
    }

    public function testGetHmacToken(): void
    {
        // Should return null when not found
        $this->assertNull($this->user->getHmacToken('foo'));

        $token = $this->user->generateHmacToken('foo');

        $found = $this->user->getHmacToken($token->secret);

        $this->assertInstanceOf(AccessToken::class, $found);
        $this->assertSame($token->id, $found->id);
        $this->assertSame($token->secret, $found->secret); // Key
        $this->assertSame($token->secret2, $found->secret2); // Secret Key
    }

    public function testGetHmacTokenById(): void
    {
        // Should return null when not found
        $this->assertNull($this->user->getHmacTokenById(123));

        $token = $this->user->generateHmacToken('foo');
        $found = $this->user->getHmacTokenById($token->id);

        $this->assertInstanceOf(AccessToken::class, $found);
        $this->assertSame($token->id, $found->id);
        $this->assertSame($token->secret, $found->secret); // Key
        $this->assertSame($token->secret2, $found->secret2); // Secret Key
    }

    public function testRevokeHmacToken(): void
    {
        $token = $this->user->generateHmacToken('foo');

        $this->assertCount(1, $this->user->hmacTokens());

        $this->user->revokeHmacToken($token->secret);

        $this->assertCount(0, $this->user->hmacTokens());
    }

    public function testRevokeAllHmacTokens(): void
    {
        $this->user->generateHmacToken('foo');
        $this->user->generateHmacToken('foo');

        $this->assertCount(2, $this->user->hmacTokens());

        $this->user->revokeAllHmacTokens();

        $this->assertCount(0, $this->user->hmacTokens());
    }

    public function testHmacTokenCanNoTokenSet(): void
    {
        $this->assertFalse($this->user->hmacTokenCan('foo'));
    }

    public function testHmacTokenCanBasics(): void
    {
        $token = $this->user->generateHmacToken('foo', ['foo:bar']);
        $this->user->setHmacToken($token);

        $this->assertTrue($this->user->hmacTokenCan('foo:bar'));
        $this->assertFalse($this->user->hmacTokenCan('foo:baz'));
    }

    public function testHmacTokenCantNoTokenSet(): void
    {
        $this->assertTrue($this->user->hmacTokenCant('foo'));
    }

    public function testHmacTokenCant(): void
    {
        $token = $this->user->generateHmacToken('foo', ['foo:bar']);
        $this->user->setHmacToken($token);

        $this->assertFalse($this->user->hmacTokenCant('foo:bar'));
        $this->assertTrue($this->user->hmacTokenCant('foo:baz'));
    }
}
