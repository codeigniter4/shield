<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Sparks\Shield\Authentication\Passwords;
use Sparks\Shield\Config\Auth as AuthConfig;
use Sparks\Shield\Entities\User;

/**
 * @internal
 */
final class PasswordsTest extends CIUnitTestCase
{
    public function testEmptyPassword()
    {
        $passwords = new Passwords(new AuthConfig());

        $result = $passwords->check('', new User());

        $this->assertFalse($result->isOK());
        $this->assertSame('A Password is required.', $result->reason());
    }
}
