<?php

declare(strict_types=1);

namespace Tests\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class UserModelGeneratorTest extends CIUnitTestCase
{
    protected $streamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';
        $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        stream_filter_remove($this->streamFilter);
        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);

        $filepath = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        if (is_file($filepath)) {
            unlink($filepath);
        }
    }

    protected function getFileContents(string $filepath): string
    {
        if (! file_exists($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testGenerateUserModel(): void
    {
        command('shield:model MyUserModel');
        $filepath = APPPATH . 'Models/MyUserModel.php';

        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($filepath);

        $this->assertStringContainsString('namespace App\Models;', $this->getFileContents($filepath));
        $this->assertStringContainsString('class MyUserModel extends UserModel', $this->getFileContents($filepath));
        $this->assertStringContainsString('use CodeIgniter\Shield\Models\UserModel;', $this->getFileContents($filepath));
        $this->assertStringContainsString('protected function initialize(): void', $this->getFileContents($filepath));
    }

    public function testGenerateUserModelCustomNamespace(): void
    {
        command('shield:model MyUserModel --namespace CodeIgniter\\\\Shield');
        $filepath = HOMEPATH . 'src/Models/MyUserModel.php';

        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($filepath);

        $this->assertStringContainsString('namespace CodeIgniter\Shield\Models;', $this->getFileContents($filepath));
        $this->assertStringContainsString('class MyUserModel extends UserModel', $this->getFileContents($filepath));
        $this->assertStringContainsString('use CodeIgniter\Shield\Models\UserModel;', $this->getFileContents($filepath));
        $this->assertStringContainsString('protected function initialize(): void', $this->getFileContents($filepath));

        if (is_file($filepath)) {
            unlink($filepath);
        }
    }

    public function testGenerateUserModelWithForce(): void
    {
        command('shield:model MyUserModel');

        command('shield:model MyUserModel --force');
        $this->assertStringContainsString('File overwritten: ', CITestStreamFilter::$buffer);

        $filepath = APPPATH . 'Models/MyUserModel.php';
        if (is_file($filepath)) {
            unlink($filepath);
        }
    }

    public function testGenerateUserModelWithSuffix(): void
    {
        command('shield:model MyUser --suffix');
        $filepath = APPPATH . 'Models/MyUserModel.php';

        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($filepath);

        $this->assertStringContainsString('class MyUserModel extends UserModel', $this->getFileContents($filepath));
    }
}
