<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Commands\Database\Migrate;
use CodeIgniter\Shield\Commands\Setup\ContentReplacer;
use Config\Services;

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
    public function run(array $params): void
    {
        $this->replacer = new ContentReplacer();

        $this->sourcePath = __DIR__ . '/../';

        $this->publishConfig();
    }

    private function publishConfig(): void
    {
        $this->publishConfigAuth();
        $this->publishConfigAuthGroups();

        $this->setupHelper();
        $this->setupRoutes();

        $this->setSecurityCSRF();

        $this->runMigrations();
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

    private function publishConfigAuth(): void
    {
        $file     = 'Config/Auth.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Config'  => 'namespace Config',
            'use CodeIgniter\\Config\\BaseConfig;' => 'use CodeIgniter\\Shield\\Config\\Auth as ShieldAuth;',
            'extends BaseConfig'                   => 'extends ShieldAuth',
        ];

        $this->copyAndReplace($file, $replaces);
    }

    private function publishConfigAuthGroups(): void
    {
        $file     = 'Config/AuthGroups.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Config'  => 'namespace Config',
            'use CodeIgniter\\Config\\BaseConfig;' => 'use CodeIgniter\\Shield\\Config\\AuthGroups as ShieldAuthGroups;',
            'extends BaseConfig'                   => 'extends ShieldAuthGroups',
        ];

        $this->copyAndReplace($file, $replaces);
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
    protected function add(string $file, string $code, string $pattern, string $replace): void
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

    /**
     * Replace for setupHelper()
     *
     * @param string $file     Relative file path like 'Controllers/BaseController.php'.
     * @param array  $replaces [search => replace]
     */
    private function replace(string $file, array $replaces): bool
    {
        $path      = $this->distPath . $file;
        $cleanPath = clean_path($path);

        $content = file_get_contents($path);

        $output = $this->replacer->replace($content, $replaces);

        if ($output === $content) {
            return false;
        }

        if (write_file($path, $output)) {
            CLI::write(CLI::color('  Updated: ', 'green') . $cleanPath);

            return true;
        }

        CLI::error("  Error updating {$cleanPath}.");

        return false;
    }

    private function setupHelper(): void
    {
        $file  = 'Controllers/BaseController.php';
        $check = '$this->helpers = array_merge($this->helpers, [\'setting\']);';

        // Replace old helper setup
        $replaces = [
            '$this->helpers = array_merge($this->helpers, [\'auth\', \'setting\']);' => $check,
        ];
        if ($this->replace($file, $replaces)) {
            return;
        }

        // Add helper setup
        $pattern = '/(' . preg_quote('// Do Not Edit This Line', '/') . ')/u';
        $replace = $check . "\n\n        " . '$1';

        $this->add($file, $check, $pattern, $replace);
    }

    private function setupRoutes(): void
    {
        $file = 'Config/Routes.php';

        $check   = 'service(\'auth\')->routes($routes);';
        $pattern = '/(.*)(\n' . preg_quote('$routes->', '/') . '[^\n]+?;\n)/su';
        $replace = '$1$2' . "\n" . $check . "\n";

        $this->add($file, $check, $pattern, $replace);
    }

    /**
     * @see https://github.com/codeigniter4/shield/security/advisories/GHSA-5hm8-vh6r-2cjq
     */
    private function setSecurityCSRF(): void
    {
        $file     = 'Config/Security.php';
        $replaces = [
            'public $csrfProtection = \'cookie\';' => 'public $csrfProtection = \'session\';',
        ];

        $path      = $this->distPath . $file;
        $cleanPath = clean_path($path);

        if (! is_file($path)) {
            CLI::error("  Not found file '{$cleanPath}'.");

            return;
        }

        $content = file_get_contents($path);
        $output  = $this->replacer->replace($content, $replaces);

        // check $csrfProtection = 'session'
        if ($output === $content) {
            CLI::write(CLI::color('  Security Setup: ', 'green') . 'Everything is fine.');

            return;
        }

        if (write_file($path, $output)) {
            CLI::write(CLI::color('  Updated: ', 'green') . "We have updated file '{$cleanPath}' for security reasons.");
        } else {
            CLI::error("  Error updating file '{$cleanPath}'.");
        }
    }

    private function runMigrations(): void
    {
        if (
            $this->cliPrompt('  Run `spark migrate --all` now?', ['y', 'n']) === 'n'
        ) {
            return;
        }

        $command = new Migrate(Services::logger(), Services::commands());
        $command->run(['all' => null]);
    }

    /**
     * This method is for testing.
     */
    protected function cliPrompt(string $field, array $options): string
    {
        return CLI::prompt($field, $options);
    }
}
