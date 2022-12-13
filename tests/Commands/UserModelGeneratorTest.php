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
    private $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');

        $this->deleteTestFiles();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        stream_filter_remove($this->streamFilter);
        $this->deleteTestFiles();
    }

    private function deleteTestFiles(): void
    {
        $possibleFiles = [
            APPPATH . 'Models/MyUserModel.php',
            HOMEPATH . 'src/Models/MyUserModel.php',
        ];

        foreach ($possibleFiles as $file) {
            clearstatcache(true, $file);

            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getFileContents(string $filepath): string
    {
        return (string) @file_get_contents($filepath);
    }

    public function testGenerateUserModel(): void
    {
        command('shield:model MyUserModel');

        $filepath = APPPATH . 'Models/MyUserModel.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($filepath);

        $contents = $this->getFileContents($filepath);
        $this->assertStringContainsString('namespace App\Models;', $contents);
        $this->assertStringContainsString('class MyUserModel extends UserModel', $contents);
        $this->assertStringContainsString('use CodeIgniter\Shield\Models\UserModel;', $contents);
        $this->assertStringContainsString('protected function initialize(): void', $contents);
    }

    public function testGenerateUserModelCustomNamespace(): void
    {
        command('shield:model MyUserModel --namespace CodeIgniter\\\\Shield');

        $filepath = HOMEPATH . 'src/Models/MyUserModel.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($filepath);

        $contents = $this->getFileContents($filepath);
        $this->assertStringContainsString('namespace CodeIgniter\Shield\Models;', $contents);
        $this->assertStringContainsString('class MyUserModel extends UserModel', $contents);
        $this->assertStringContainsString('use CodeIgniter\Shield\Models\UserModel;', $contents);
        $this->assertStringContainsString('protected function initialize(): void', $contents);
    }

    public function testGenerateUserModelWithForce(): void
    {
        command('shield:model MyUserModel');
        command('shield:model MyUserModel --force');

        $this->assertStringContainsString('File overwritten: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Models/MyUserModel.php');
    }

    public function testGenerateUserModelWithSuffix(): void
    {
        command('shield:model MyUser --suffix');

        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);

        $filepath = APPPATH . 'Models/MyUserModel.php';
        $this->assertFileExists($filepath);
        $this->assertStringContainsString('class MyUserModel extends UserModel', $this->getFileContents($filepath));
    }
}
