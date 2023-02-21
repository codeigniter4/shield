<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use Config\Services;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class EmailActivatorTest extends TestCase
{
    public function testShowCannotGetPendingLoginUser(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get the pending login User.');

        $activator = new EmailActivator();

        $activator->show();
    }

    public function testVerifyCannotGetPendingLoginUser(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get the pending login User.');

        $activator = new EmailActivator();
        $request   = Services::incomingrequest(null, false);

        $activator->verify($request);
    }
}
