<?php

namespace Test\Authentication;

use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Models\UserModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthHelperTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['auth']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setPrivateProperty(auth(), 'handler', null);
    }

    public function testAuthReturnsDefaultHandler()
    {
        $handlerName = config('Auth')->authenticators[config('Auth')->defaultAuthenticator];

        $this->assertInstanceOf($handlerName, auth()->getAuthenticator());
    }

    public function testAuthReturnsSpecifiedHandler()
    {
        $handlerName = config('Auth')->authenticators['tokens'];

        $this->assertInstanceOf($handlerName, auth('tokens')->getAuthenticator());
    }

    public function testAuthThrowsWithInvalidHandler()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.unknownHandler', ['foo']));

        auth('foo')->user();
    }

    public function testUserIdReturnsNull()
    {
        $this->assertFalse(auth()->loggedIn());
        $this->assertNull(user_id());
    }

    public function testUserIdReturnsId()
    {
        $user = fake(UserModel::class, [], false);
        $this->setPrivateProperty(auth()->getAuthenticator(), 'user', $user);

        $this->assertTrue(auth()->loggedIn());
        $this->assertSame($user->id, user_id());
    }
}
