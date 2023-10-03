<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\Shield\Commands\Setup;
use CodeIgniter\Shield\Test\MockInputOutput;
use Config\Email as EmailConfig;
use Config\Services;
use org\bovigo\vfs\vfsStream;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class SetupTest extends TestCase
{
    private ?MockInputOutput $io = null;

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

        $appFolder = $this->createFilesystem();

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

        $result = $this->getOutputWithoutColorCode();

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

    public function testRunEmailConfigIsFine(): void
    {
        // Set MockIO and your inputs.
        $this->setMockIo(['y']);

        $config            = config(EmailConfig::class);
        $config->fromEmail = 'admin@example.com';
        $config->fromName  = 'Site Admin';

        $appFolder = $this->createFilesystem();

        $command = new Setup(Services::logger(), Services::commands());

        $this->setPrivateProperty($command, 'distPath', $appFolder);

        $command->run([]);

        $result = $this->getOutputWithoutColorCode();

        $this->assertStringContainsString(
            '  Created: vfs://root/Config/Auth.php
  Created: vfs://root/Config/AuthGroups.php
  Created: vfs://root/Config/AuthToken.php
  Updated: vfs://root/Controllers/BaseController.php
  Updated: vfs://root/Config/Routes.php
  Updated: We have updated file \'vfs://root/Config/Security.php\' for security reasons.',
            $result
        );
    }

    /**
     * @return string app folder path
     */
    private function createFilesystem(): string
    {
        $root = vfsStream::setup('root');
        vfsStream::copyFromFileSystem(
            APPPATH,
            $root
        );

        return $root->url() . '/';
    }

    private function getOutputWithoutColorCode(): string
    {
        return str_replace(["\033[0;32m", "\033[0m"], '', $this->io->getOutputs());
    }
}
