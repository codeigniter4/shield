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

use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
abstract class DatabaseTestCase extends TestCase
{
    use DatabaseTestTrait;

    protected $namespace = '\CodeIgniter\Shield';

    /**
     * Auth Table names
     */
    protected array $tables;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Auth $authConfig */
        $authConfig   = config('Auth');
        $this->tables = $authConfig->tables;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
