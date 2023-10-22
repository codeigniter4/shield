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

namespace Tests\Authentication;

use CodeIgniter\Shield\Config\Services;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class AuthTest extends DatabaseTestCase
{
    public function testAuthenticateFail(): void
    {
        $auth = Services::auth();

        $credentials = ['email' => ''];
        $result      = $auth->authenticate($credentials);

        $this->assertFalse($result->isOK());
    }
}
