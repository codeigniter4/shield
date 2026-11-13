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

namespace CodeIgniter\Shield\Tests\Unit;

use CodeIgniter\Shield\Authentication\Passwords\ValidationRules;
use CodeIgniter\Shield\Tests\DatabaseTestCase;
use CodeIgniter\Shield\Tests\FakeUser;

/**
 * @internal
 */
final class ValidationRulesTest extends DatabaseTestCase
{
    use FakeUser;

    protected $namespace;
    protected $refresh = true;

    public function testStrongPasswordUsesLoggedInUser(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'jane.doe@example.com',
            'password' => 'secret123',
        ]);
        auth()->login($this->user);

        $error = null;

        $this->assertFalse((new ValidationRules())->strong_password('jane.doe@example.com', $error));
        $this->assertSame(lang('Auth.errorPasswordPersonal'), $error);
    }

    public function testStrongPasswordUsesAssignedDataWithoutLoggedInUser(): void
    {
        $error1 = null;
        $error2 = null;

        $result = (new ValidationRules())->strong_password(
            'jane.doe@example.com',
            $error1,
            ['email' => 'jane.doe@example.com'],
            $error2,
        );

        $this->assertFalse($result);
        $this->assertNull($error1);
        $this->assertSame(lang('Auth.errorPasswordPersonal'), $error2);
    }
}
