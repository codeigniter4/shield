<?php

namespace Tests\Authentication;

use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthHelperTest extends TestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['auth']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setPrivateProperty(auth(), 'alias', null);
    }

    public function testAuthReturnsDefaultAuthenticator(): void
    {
        $authenticatorClassname = config('Auth')->authenticators[config('Auth')->defaultAuthenticator];

        $this->assertInstanceOf($authenticatorClassname, auth()->getAuthenticator());
    }

    public function testAuthReturnsSpecifiedAuthenticator(): void
    {
        $authenticatorClassname = config('Auth')->authenticators['tokens'];

        $this->assertInstanceOf($authenticatorClassname, auth('tokens')->getAuthenticator());
    }

    public function testAuthThrowsWithInvalidAuthenticator(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.unknownAuthenticator', ['foo']));

        auth('foo')->user();
    }

    public function testUserIdReturnsNull(): void
    {
        $this->assertFalse(auth()->loggedIn());
        $this->assertNull(user_id());
    }

    public function testUserIdReturnsId(): void
    {
        $user = fake(UserModel::class, ['id' => 1]);
        $this->setPrivateProperty(auth()->getAuthenticator(), 'user', $user);
        $_SESSION['user']['id'] = $user->id;

        $this->assertTrue(auth()->loggedIn());
        $this->assertSame($user->id, user_id());
    }
}
