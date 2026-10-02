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

namespace Tests\Commands;

use CodeIgniter\Shield\Commands\GenerateDummyPasswordHash;
use CodeIgniter\Shield\Test\MockInputOutput;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class GenerateDummyPasswordHashTest extends TestCase
{
    protected function tearDown(): void
    {
        GenerateDummyPasswordHash::resetInputOutput();

        parent::tearDown();
    }

    public function testGeneratesHashUsingCurrentPasswordSettings(): void
    {
        $config           = config('Auth');
        $config->hashCost = 5;

        $hash = $this->generateHash();

        $this->assertSame(PASSWORD_BCRYPT, password_get_info($hash)['algo']);
        $this->assertSame(5, password_get_info($hash)['options']['cost']);
        $this->assertFalse(service('passwords')->needsRehash($hash));
    }

    public function testGeneratesHashUsingCurrentArgon2idSettings(): void
    {
        if (! defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('Argon2id is not available.');
        }

        $config                 = config('Auth');
        $config->hashAlgorithm  = PASSWORD_ARGON2ID;
        $config->hashMemoryCost = 8192;
        $config->hashTimeCost   = 2;
        $config->hashThreads    = 1;

        $hash = $this->generateHash();
        $info = password_get_info($hash);

        $this->assertSame(PASSWORD_ARGON2ID, $info['algo']);
        $this->assertSame(8192, $info['options']['memory_cost']);
        $this->assertSame(2, $info['options']['time_cost']);
        $this->assertFalse(service('passwords')->needsRehash($hash));
    }

    private function generateHash(): string
    {
        $io = new MockInputOutput();
        GenerateDummyPasswordHash::setInputOutput($io);

        $this->assertNotFalse(command('shield:generate-dummy-hash'));

        $output = trim($io->getOutputs());
        $prefix = 'public string $dummyPasswordHash = \'';
        $this->assertStringStartsWith($prefix, $output);
        $this->assertStringEndsWith("';", $output);

        return substr($output, strlen($prefix), -2);
    }
}
