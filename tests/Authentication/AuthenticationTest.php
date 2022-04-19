<?php

namespace Tests\Authentication;

use CodeIgniter\Test\DatabaseTestTrait;
use Sparks\Shield\Authentication\Authentication;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Authentication\Handlers\Session;
use Sparks\Shield\Config\Auth;
use Sparks\Shield\Models\UserModel;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthenticationTest extends TestCase
{
    use DatabaseTestTrait;

    protected Authentication $auth;
    protected $namespace = ['CodeIgniter\Settings', '\Sparks\Shield'];

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
        $this->expectExceptionMessage(lang('Auth.unknownHandler', ['foo']));

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
