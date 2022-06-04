<?php

namespace CodeIgniter\Shield\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;

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

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params)
    {
        if (! $this->determineSourcePath()) {
            return;
        }

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

        foreach ($replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        $this->writeFile($file, $content);
    }

    private function publishConfigAuth()
    {
        $file     = 'Config/Auth.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Config'    => 'namespace Config',
            "use CodeIgniter\\Config\\BaseConfig;\n" => '',
            'extends BaseConfig'                     => 'extends \\CodeIgniter\\Shield\\Config\\Auth',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    private function publishConfigAuthGroups()
    {
        $file     = 'Config/AuthGroups.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Config'    => 'namespace Config',
            "use CodeIgniter\\Config\\BaseConfig;\n" => '',
            'extends BaseConfig'                     => 'extends \\CodeIgniter\\Shield\\Config\\AuthGroups',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    /**
     * Determines the current source path from which all other files are located.
     */
    private function determineSourcePath(): bool
    {
        $this->sourcePath = realpath(__DIR__ . '/../');

        if ($this->sourcePath === '/' || $this->sourcePath === false) {
            CLI::error('Unable to determine the correct source directory. Bailing.');

            return false;
        }

        return true;
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     *
     * @param string $file Relative file path like 'Config/Auth.php'.
     */
    protected function writeFile(string $file, string $content)
    {
        $config  = new Autoload();
        $appPath = $config->psr4[APP_NAMESPACE];

        $path      = $appPath . $file;
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
     * @param string $check Code to add.
     * @param string $file  Relative file path like 'Controllers/BaseController.php'.
     */
    protected function replace(string $file, string $check, string $pattern, string $replace)
    {
        $config    = new Autoload();
        $appPath   = $config->psr4[APP_NAMESPACE];
        $path      = $appPath . $file;
        $cleanPath = clean_path($path);

        $content = file_get_contents($path);

        $return = preg_match('/' . preg_quote($check, '/') . '/u', $content);
        if ($return === 1) {
            CLI::error("  Skipped {$cleanPath}. It has already been updated.");

            return;
        }
        if ($return === false) {
            CLI::error("  Error checking {$cleanPath}.");

            return;
        }

        $content = preg_replace($pattern, $replace, $content);

        if (write_file($path, $content)) {
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

        $this->replace($file, $check, $pattern, $replace);
    }

    private function setupRoutes()
    {
        $file = 'Config/Routes.php';

        $check   = 'service(\'auth\')->routes($routes);';
        $pattern = '/(.*)(\n' . preg_quote('$routes->', '/') . '[^\n]+?\n)/su';
        $replace = '$1$2' . "\n" . $check . "\n";

        $this->replace($file, $check, $pattern, $replace);
    }
}
