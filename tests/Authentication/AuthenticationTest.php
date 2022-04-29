<?php

namespace Tests\Authentication;

use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Models\UserModel;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class AuthenticationTest extends DatabaseTestCase
{
    protected Authentication $auth;

    protected function setUp(): void
    {
        parent::setUp();

        $config     = new Auth();
        $this->auth = new Authentication($config);
        $this->auth->setProvider(new UserModel());
    }

    public function testThrowsOnUnknownHandler()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.unknownAuthenticator', ['foo']));

        $this->auth->factory('foo');
    }

    public function testFactoryLoadsDefault()
    {
        $shield1 = $this->auth->factory();
        $shield2 = $this->auth->factory('session');

        $this->assertInstanceOf(Session::class, $shield1);
        $this->assertSame($shield1, $shield2);
    }
}
