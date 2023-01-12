<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\CodeIgniter;
use CodeIgniter\Shield\Commands\Setup;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Services;
use org\bovigo\vfs\vfsStream;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class SetupTest extends TestCase
{
    private $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare(CodeIgniter::CI_VERSION, '4.3.0', '>=')) {
            CITestStreamFilter::registration();
            CITestStreamFilter::addOutputFilter();
        } else {
            CITestStreamFilter::$buffer = '';
            $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (version_compare(CodeIgniter::CI_VERSION, '4.3.0', '>=')) {
            CITestStreamFilter::removeOutputFilter();
            CITestStreamFilter::removeErrorFilter();
        } else {
            stream_filter_remove($this->streamFilter);
        }
    }

    public function testRun(): void
    {
        $root = vfsStream::setup('root');
        vfsStream::copyFromFileSystem(
            APPPATH,
            $root
        );
        $appFolder = $root->url() . '/';

        $command = $this->getMockBuilder(Setup::class)
            ->setConstructorArgs([Services::logger(), Services::commands()])
            ->onlyMethods(['cliPrompt'])
            ->getMock();
        $command
            ->method('cliPrompt')
            ->willReturn('y');

        $this->setPrivateProperty($command, 'distPath', $appFolder);

        $command->run([]);

        $auth = file_get_contents($appFolder . 'Config/Auth.php');
        $this->assertStringContainsString('namespace Config;', $auth);
        $this->assertStringContainsString('use CodeIgniter\Shield\Config\Auth as ShieldAuth;', $auth);

        $routes = file_get_contents($appFolder . 'Config/Routes.php');
        $this->assertStringContainsString('service(\'auth\')->routes($routes);', $routes);

        $security = file_get_contents($appFolder . 'Config/Security.php');
        $this->assertStringContainsString('$csrfProtection = \'session\';', $security);

        $result = str_replace(["\033[0;32m", "\033[0m"], '', CITestStreamFilter::$buffer);

        $this->assertStringContainsString(
            '  Created: vfs://root/Config/Auth.php
  Created: vfs://root/Config/AuthGroups.php
  Updated: vfs://root/Controllers/BaseController.php
  Updated: vfs://root/Config/Routes.php
  Updated: We have updated file \'vfs://root/Config/Security.php\' for security reasons.',
            $result
        );
        $this->assertStringContainsString(
            'Running all new migrations...',
            $result
        );
    }
}
