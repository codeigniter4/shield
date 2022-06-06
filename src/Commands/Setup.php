<?php

namespace CodeIgniter\Shield\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Commands\Setup\ContentReplacer;

class Setup extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Shield';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'shield:setup';

    /**
     * the Command's short description
     *
     * @var string
     */
    protected $description = 'Initial setup for CodeIgniter Shield.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'shield:setup';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [
        '-f' => 'Force overwrite ALL existing files in destination.',
    ];

    /**
     * The path to `CodeIgniter\Shield\` src directory.
     *
     * @var string
     */
    protected $sourcePath;

    protected $distPath = APPPATH;
    private ContentReplacer $replacer;

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        $this->replacer = new ContentReplacer();

        $this->sourcePath = __DIR__ . '/../';

        $this->publishConfig();
    }

    private function publishConfig()
    {
        $this->publishConfigAuth();
        $this->publishConfigAuthGroups();

        $this->setupHelper();
        $this->setupRoutes();
    }

    /**
     * @param string $file     Relative file path like 'Config/Auth.php'.
     * @param array  $replaces [search => replace]
     */
    protected function copyAndReplace(string $file, array $replaces)
    {
        $path = "{$this->sourcePath}/{$file}";

        $content = file_get_contents($path);

        $content = $this->replacer->replace($content, $replaces);

        $this->writeFile($file, $content);
    }

    private function publishConfigAuth()
    {
        $file     = 'Config/Auth.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Config'    => 'namespace Config',
            "use CodeIgniter\\Config\\BaseConfig;" => 'CodeIgniter\\Shield\\Config\\Auth as ShieldAuth;',
            'extends BaseConfig'                     => 'extends ShieldAuth',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    private function publishConfigAuthGroups()
    {
        $file     = 'Config/AuthGroups.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Config'    => 'namespace Config',
            "use CodeIgniter\\Config\\BaseConfig;" => 'CodeIgniter\\Shield\\Config\\AuthGroups as ShieldAuthGroups;',
            'extends BaseConfig'                     => 'extends ShieldAuthGroups',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     *
     * @param string $file Relative file path like 'Config/Auth.php'.
     */
    protected function writeFile(string $file, string $content)
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
                && CLI::prompt("  File '{$cleanPath}' already exists in destination. Overwrite?", ['n', 'y']) === 'n'
            ) {
                CLI::error("  Skipped {$cleanPath}. If you wish to overwrite, please use the '-f' option or reply 'y' to the prompt.");

                return;
            }
        }

        if (write_file($path, $content)) {
            CLI::write(CLI::color('  Created: ', 'green') . $cleanPath);
        } else {
            CLI::error("  Error creating {$cleanPath}.");
        }
    }

    /**
     * @param string $code Code to add.
     * @param string $file Relative file path like 'Controllers/BaseController.php'.
     */
    protected function add(string $file, string $code, string $pattern, string $replace)
    {
        $path      = $this->distPath . $file;
        $cleanPath = clean_path($path);

        $content = file_get_contents($path);

        $output = $this->replacer->add($content, $code, $pattern, $replace);

        if ($output === true) {
            CLI::error("  Skipped {$cleanPath}. It has already been updated.");

            return;
        }
        if ($output === false) {
            CLI::error("  Error checking {$cleanPath}.");

            return;
        }

        if (write_file($path, $output)) {
            CLI::write(CLI::color('  Updated: ', 'green') . $cleanPath);
        } else {
            CLI::error("  Error updating {$cleanPath}.");
        }
    }

    private function setupHelper()
    {
        $file = 'Controllers/BaseController.php';

        $check   = '$this->helpers = array_merge($this->helpers, [\'auth\', \'setting\']);';
        $pattern = '/(' . preg_quote('// Do Not Edit This Line', '/') . ')/u';
        $replace = $check . "\n\n        " . '$1';

        $this->add($file, $check, $pattern, $replace);
    }

    private function setupRoutes()
    {
        $file = 'Config/Routes.php';

        $check   = 'service(\'auth\')->routes($routes);';
        $pattern = '/(.*)(\n' . preg_quote('$routes->', '/') . '[^\n]+?\n)/su';
        $replace = '$1$2' . "\n" . $check . "\n";

        $this->add($file, $check, $pattern, $replace);
    }
}
