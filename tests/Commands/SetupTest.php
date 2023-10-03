<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\Shield\Commands\Setup;
use CodeIgniter\Shield\Test\MockInputOutput;
use Config\Services;
use org\bovigo\vfs\vfsStream;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class SetupTest extends TestCase
{
    private ?MockInputOutput $io = null;
    private $streamFilter;

    protected function tearDown(): void
    {
        parent::tearDown();

        Setup::resetInputOutput();
    }

    /**
     * Set MockInputOutput and user inputs.
     *
     * @param array<int, string> $inputs User inputs
     * @phpstan-param list<string> $inputs
     */
    private function setMockIo(array $inputs): void
    {
        $this->io = new MockInputOutput();
        $this->io->setInputs($inputs);
        Setup::setInputOutput($this->io);
    }

    public function testRun(): void
    {
        // Set MockIO and your inputs.
        $this->setMockIo([
            'y',
            'admin@example.com',
            'y',
            'Site Administrator',
            'y',
        ]);

        $root = vfsStream::setup('root');
        vfsStream::copyFromFileSystem(
            APPPATH,
            $root
        );
        $appFolder = $root->url() . '/';

        $command = new Setup(Services::logger(), Services::commands());

        $this->setPrivateProperty($command, 'distPath', $appFolder);

        $command->run([]);

        $auth = file_get_contents($appFolder . 'Config/Auth.php');
        $this->assertStringContainsString('namespace Config;', $auth);
        $this->assertStringContainsString('use CodeIgniter\Shield\Config\Auth as ShieldAuth;', $auth);

        $authToken = file_get_contents($appFolder . 'Config/AuthToken.php');
        $this->assertStringContainsString('namespace Config;', $authToken);
        $this->assertStringContainsString('use CodeIgniter\Shield\Config\AuthToken as ShieldAuthToken;', $authToken);

        $routes = file_get_contents($appFolder . 'Config/Routes.php');
        $this->assertStringContainsString('service(\'auth\')->routes($routes);', $routes);

        $security = file_get_contents($appFolder . 'Config/Security.php');
        $this->assertStringContainsString('$csrfProtection = \'session\';', $security);

        $result = str_replace(["\033[0;32m", "\033[0m"], '', $this->io->getOutputs());

        $this->assertStringContainsString(
            '  Created: vfs://root/Config/Auth.php
  Created: vfs://root/Config/AuthGroups.php
  Created: vfs://root/Config/AuthToken.php
  Updated: vfs://root/Controllers/BaseController.php
  Updated: vfs://root/Config/Routes.php
  Updated: We have updated file \'vfs://root/Config/Security.php\' for security reasons.
  Updated: vfs://root/Config/Email.php',
            $result
        );
        $this->assertStringContainsString(
            'Running all new migrations...',
            $result
        );
    }
}
