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

namespace CodeIgniter\Shield\Tests\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @internal
 */
final class UserModelGeneratorTest extends CIUnitTestCase
{
    private const NAMESPACES = [
        'App'                => 'app',
        'CodeIgniter\Shield' => 'src',
    ];

    private vfsStreamDirectory $root;

    /**
     * @var array<string, list<string>>
     */
    private array $originalNamespaces = [];

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();
        CITestStreamFilter::addErrorFilter();

        $this->root = vfsStream::setup('root');

        $autoloader = service('autoloader');

        foreach (self::NAMESPACES as $namespace => $directory) {
            $this->originalNamespaces[$namespace] = $autoloader->getNamespace($namespace);

            $autoloader->removeNamespace($namespace);
            $autoloader->addNamespace([
                $namespace => [$this->root->url() . '/' . $directory, ...$this->originalNamespaces[$namespace]],
            ]);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CITestStreamFilter::removeOutputFilter();
        CITestStreamFilter::removeErrorFilter();

        $autoloader = service('autoloader');

        foreach ($this->originalNamespaces as $namespace => $paths) {
            $autoloader->removeNamespace($namespace);
            $autoloader->addNamespace([$namespace => $paths]);
        }
    }

    private function getFileContents(string $filepath): string
    {
        return (string) @file_get_contents($filepath);
    }

    public function testGenerateUserModel(): void
    {
        command('shield:model UserModel');

        $filepath = $this->root->url() . '/app/Models/UserModel.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($filepath);

        $contents = $this->getFileContents($filepath);
        $this->assertStringContainsString('namespace App\Models;', $contents);
        $this->assertStringContainsString('class UserModel extends ShieldUserModel', $contents);
        $this->assertStringContainsString('use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;', $contents);
        $this->assertStringContainsString('protected function initialize(): void', $contents);
        $this->assertStringContainsString('parent::initialize();', $contents);
    }

    public function testGenerateUserModelCustomNamespace(): void
    {
        command('shield:model UserModel --namespace CodeIgniter\\\\Shield');

        $filepath = $this->root->url() . '/src/Models/UserModel.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($filepath);

        $contents = $this->getFileContents($filepath);
        $this->assertStringContainsString('namespace CodeIgniter\Shield\Models;', $contents);
        $this->assertStringContainsString('class UserModel extends ShieldUserModel', $contents);
        $this->assertStringContainsString('use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;', $contents);
        $this->assertStringContainsString('protected function initialize(): void', $contents);
    }

    public function testGenerateUserModelWithForce(): void
    {
        command('shield:model UserModel');
        command('shield:model UserModel --force');

        $this->assertStringContainsString('File overwritten: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($this->root->url() . '/app/Models/UserModel.php');
    }

    public function testGenerateUserModelWithSuffix(): void
    {
        command('shield:model User --suffix');

        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);

        $filepath = $this->root->url() . '/app/Models/UserModel.php';
        $this->assertFileExists($filepath);
        $this->assertStringContainsString('class UserModel extends ShieldUserModel', $this->getFileContents($filepath));
    }

    public function testGenerateUserModelWithoutClassNameInput(): void
    {
        command('shield:model');

        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);

        $filepath = $this->root->url() . '/app/Models/UserModel.php';
        $this->assertFileExists($filepath);
        $this->assertStringContainsString('class UserModel extends ShieldUserModel', $this->getFileContents($filepath));
    }

    public function testGenerateUserCannotAcceptShieldUserModelAsInput(): void
    {
        command('shield:model ShieldUserModel');

        $this->assertStringContainsString('Cannot use `ShieldUserModel` as class name as this conflicts with the parent class.', CITestStreamFilter::$buffer);
        $this->assertFileDoesNotExist($this->root->url() . '/app/Models/UserModel.php');

        CITestStreamFilter::$buffer = '';

        command('shield:model ShieldUser --suffix');

        $this->assertStringContainsString('Cannot use `ShieldUserModel` as class name as this conflicts with the parent class.', CITestStreamFilter::$buffer);
        $this->assertFileDoesNotExist($this->root->url() . '/app/Models/UserModel.php');
    }
}
