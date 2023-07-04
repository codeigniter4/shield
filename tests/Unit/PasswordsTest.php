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

    public function testHash(): string
    {
        $config    = new AuthConfig();
        $passwords = new Passwords($config);

        $plainPassword  = 'passw0rd!';
        $hashedPassword = $passwords->hash($plainPassword);

        $user = new User([
            'id'       => 1,
            'username' => 'John',
        ]);
        $user->email         = 'john@example.org';
        $user->password_hash = $hashedPassword;

        $result = $passwords->check($plainPassword, $user);

        $this->assertTrue($result->isOK());

        return $hashedPassword;
    }

    /**
     * @depends testHash
     */
    public function testNeedsRehashTakesCareOptions(string $hashedPassword): void
    {
        $config           = new AuthConfig();
        $config->hashCost = 12;
        $passwords        = new Passwords($config);

        $result = $passwords->needsRehash($hashedPassword);

        $this->assertTrue($result);
    }
}
