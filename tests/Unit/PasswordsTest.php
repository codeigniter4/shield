<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Config\Auth as AuthConfig;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PasswordsTest extends CIUnitTestCase
{
    public function testEmptyPassword(): void
    {
        $passwords = new Passwords(new AuthConfig());

        $result = $passwords->check('', new User());

        $this->assertFalse($result->isOK());
        $this->assertSame('A Password is required.', $result->reason());
    }
}
