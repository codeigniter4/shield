<?php

namespace Tests\Support;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
abstract class TestCase extends CIUnitTestCase
{
    protected function setUp(): void
    {
        $this->resetServices();

        parent::setUp();

        // Ensure from email is avialble anywhere during Tests
        helper('setting');
        setting('Email.fromEmail', 'foo@example.com');
        setting('Email.fromName', 'John Smith');

        // Clear any actions
        $config          = config('Auth');
        $config->actions = ['login' => null, 'register' => null];
        Factories::injectMock('config', 'Auth', $config);
    }
}
