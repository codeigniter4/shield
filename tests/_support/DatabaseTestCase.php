<?php

declare(strict_types=1);

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
