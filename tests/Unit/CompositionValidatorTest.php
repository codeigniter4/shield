<?php

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\Passwords\CompositionValidator;
use CodeIgniter\Shield\Config\Auth;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class CompositionValidatorTest extends TestCase
{
    protected CompositionValidator $validator;
    protected Auth $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config                        = new Auth();
        $this->config->minimumPasswordLength = 8;

        $this->validator = new CompositionValidator($this->config);
    }

    public function testCheckThrowsException()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.unsetPasswordLength'));

        $password                            = '1234';
        $this->config->minimumPasswordLength = 0;

        $this->validator->check($password);
    }

    public function testCheckFalse()
    {
        $password = '1234';

        $result = $this->validator->check($password);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.errorPasswordLength', [$this->config->minimumPasswordLength]), $result->reason());
    }

    public function testCheckTrue()
    {
        $password = '1234567890';

        $result = $this->validator->check($password);

        $this->assertTrue($result->isOK());
    }
}
