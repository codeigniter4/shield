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

namespace Tests\Support;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Shield\Authentication\Actions\ActionInterface;
use CodeIgniter\Shield\Entities\User;

/**
 * @internal
 */
// Simulate new action

final class FakeAction implements ActionInterface
{
    private string $type = 'test';

    public function show(): string
    {
        return 'show';
    }

    public function handle(IncomingRequest $request): string
    {
        return 'handle';
    }

    public function verify(IncomingRequest $request): string
    {
        return 'verify';
    }

    public function getActionMessage(): string
    {
        return 'Finish 2FA';
    }

    public function createIdentity(User $user): string
    {
        return 'created!';
    }

    public function getType(): string
    {
        return $this->type;
    }
}
