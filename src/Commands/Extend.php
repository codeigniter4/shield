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

namespace CodeIgniter\Shield\Commands;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Commands\Setup\ContentReplacer;

class Extend extends BaseCommand
{
    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'shield:extend';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Extending the Controllers.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'shield:extend';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '-i' => 'The index of shield controllers to be extending in your app.',
        '-f' => 'Force overwrite ALL existing files in destination.',
    ];

    protected $sourcePath;

    protected $distPath = APPPATH;
    private ContentReplacer $replacer;

    private const INFO_MESSAGE    = "  After extending, don't forget to change the route. See https://shield.codeigniter.com/customization/route_config";

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $this->replacer = new ContentReplacer();
        $this->sourcePath = __DIR__ . '/../../examples/';
        // Get option -i value
        $index = CLI::getOption('i');

        // if no option -i provided show this prompt to user
        if($params === [] || array_key_exists('i', $params) === false || $params['i'] === null ) {
            $this->write('List of the controller that will be extend:');
            $this->write();
            $this->write('  [1] LoginController');
            $this->write('  [2] MagicLinkController');
            $this->write('  [3] RegisterController');
            $this->write();
            $index = $this->prompt('Please select one of these (1/2/3)');
        }

        switch ((int) $index) {
            case 1:
                $this->extendingLoginController();
                break;
            case 2:
                $this->extendingMagicLinkController();
                break;
            case 3:
                $this->extendingRegisterController();
                break;
            
            default:
                $this->write();
                CLI::error("  Extending canceled: your input not match with any index.");
                $this->write();
                break;
        }

        return 0;
    }

    private function extendingLoginController() 
    {
        $file     = 'Controllers/LoginController.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Controllers' => 'namespace App\Controllers',
            'use App\\Controllers\\BaseController'     => 'use CodeIgniter\\Shield\\Controllers\\LoginController as ShieldLoginController',
            'extends BaseController'                   => 'extends ShieldLoginController',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    private function extendingMagicLinkController() 
    {
        $file     = 'Controllers/MagicLinkController.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Controllers' => 'namespace App\Controllers',
            'use App\\Controllers\\BaseController'     => 'use CodeIgniter\\Shield\\Controllers\\MagicLinkController as ShieldMagicLinkController',
            'extends BaseController'                   => 'extends ShieldMagicLinkController',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    private function extendingRegisterController() 
    {
        $file     = 'Controllers/RegisterController.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Controllers' => 'namespace App\Controllers',
            'use App\\Controllers\\BaseController'     => 'use CodeIgniter\\Shield\\Controllers\\RegisterController as ShieldRegisterController',
            'extends BaseController'                   => 'extends ShieldRegisterController',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    /**
     * @param string $file     Relative file path like 'Config/Auth.php'.
     * @param array  $replaces [search => replace]
     */
    protected function copyAndReplace(string $file, array $replaces): void
    {
        $path = "{$this->sourcePath}/{$file}";

        $content = file_get_contents($path);

        $content = $this->replacer->replace($content, $replaces);

        $this->writeFile($file, $content);
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     *
     * @param string $file Relative file path like 'Config/Auth.php'.
     */
    protected function writeFile(string $file, string $content): void
    {
        $path      = $this->distPath . $file;
        $cleanPath = clean_path($path);

        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (file_exists($path)) {
            $overwrite = (bool) CLI::getOption('f');

            if (
                ! $overwrite
                && $this->prompt("  File '{$cleanPath}' already exists in destination. Overwrite?", ['n', 'y']) === 'n'
            ) {
                $this->error("  Skipped {$cleanPath}. If you wish to overwrite, please use the '-f' option or reply 'y' to the prompt.");

                return;
            }
        }

        if (write_file($path, $content)) {
            $this->write();
            $this->write(CLI::color('  Created: ', 'green') . $cleanPath);
            $this->write(self::INFO_MESSAGE, 'light_green');
            $this->write();
        } else {
            $this->error("  Error creating {$cleanPath}.");
        }
    }
}
