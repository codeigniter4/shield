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
use CodeIgniter\Commands\Database\Migrate;
use CodeIgniter\Shield\Commands\Setup\ContentReplacer;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use Config\Autoload as AutoloadConfig;
use Config\Email as EmailConfig;
use Config\Services;

class Setup extends BaseCommand
{
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
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * the Command's Options
     *
     * @var array<string, string>
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
        $this->publishConfigAuthToken();

        $this->setAutoloadHelpers();
        $this->setupRoutes();

        $this->setSecurityCSRF();
        $this->setupEmail();

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

    private function publishConfigAuthToken(): void
    {
        $file     = 'Config/AuthToken.php';
        $replaces = [
            'namespace CodeIgniter\Shield\Config;' => "namespace Config;\n\nuse CodeIgniter\\Shield\\Config\\AuthToken as ShieldAuthToken;",
            'extends BaseAuthToken'                => 'extends ShieldAuthToken',
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
                && $this->prompt("  File '{$cleanPath}' already exists in destination. Overwrite?", ['n', 'y']) === 'n'
            ) {
                $this->error("  Skipped {$cleanPath}. If you wish to overwrite, please use the '-f' option or reply 'y' to the prompt.");

                return;
            }
        }

        if (write_file($path, $content)) {
            $this->write(CLI::color('  Created: ', 'green') . $cleanPath);
        } else {
            $this->error("  Error creating {$cleanPath}.");
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
            $this->error("  Skipped {$cleanPath}. It has already been updated.");

            return;
        }
        if ($output === false) {
            $this->error("  Error checking {$cleanPath}.");

            return;
        }

        if (write_file($path, $output)) {
            $this->write(CLI::color('  Updated: ', 'green') . $cleanPath);
        } else {
            $this->error("  Error updating {$cleanPath}.");
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
            $this->write(CLI::color('  Updated: ', 'green') . $cleanPath);

            return true;
        }

        $this->error("  Error updating {$cleanPath}.");

        return false;
    }

    private function setAutoloadHelpers(): void
    {
        $file = 'Config/Autoload.php';

        $path      = $this->distPath . $file;
        $cleanPath = clean_path($path);

        $config     = new AutoloadConfig();
        $helpers    = $config->helpers;
        $newHelpers = array_unique(array_merge($helpers, ['auth', 'setting']));

        $pattern = '/^    public \$helpers = \[.*\];/mu';
        $replace = '    public $helpers = [\'' . implode("', '", $newHelpers) . '\'];';
        $content = file_get_contents($path);
        $output  = preg_replace($pattern, $replace, $content);

        // check if the content is updated
        if ($output === $content) {
            $this->write(CLI::color('  Autoload Setup: ', 'green') . 'Everything is fine.');

            return;
        }

        if (write_file($path, $output)) {
            $this->write(CLI::color('  Updated: ', 'green') . $cleanPath);

            $this->removeHelperLoadingInBaseController();
        } else {
            $this->error("  Error updating file '{$cleanPath}'.");
        }
    }

    private function removeHelperLoadingInBaseController(): void
    {
        $file = 'Controllers/BaseController.php';

        $check = '        $this->helpers = array_merge($this->helpers, [\'setting\']);';

        // Replace old helper setup
        $replaces = [
            '$this->helpers = array_merge($this->helpers, [\'auth\', \'setting\']);' => $check,
        ];
        $this->replace($file, $replaces);

        // Remove helper setup
        $replaces = [
            "\n" . $check . "\n" => '',
        ];
        $this->replace($file, $replaces);
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
            '$csrfProtection = \'cookie\';' => '$csrfProtection = \'session\';',
        ];

        $path      = $this->distPath . $file;
        $cleanPath = clean_path($path);

        if (! is_file($path)) {
            $this->error("  Not found file '{$cleanPath}'.");

            return;
        }

        $content = file_get_contents($path);
        $output  = $this->replacer->replace($content, $replaces);

        // check $csrfProtection = 'session'
        if ($output === $content) {
            $this->write(CLI::color('  Security Setup: ', 'green') . 'Everything is fine.');

            return;
        }

        if (write_file($path, $output)) {
            $this->write(CLI::color('  Updated: ', 'green') . "We have updated file '{$cleanPath}' for security reasons.");
        } else {
            $this->error("  Error updating file '{$cleanPath}'.");
        }
    }

    private function setupEmail(): void
    {
        $file = 'Config/Email.php';

        $path      = $this->distPath . $file;
        $cleanPath = clean_path($path);

        if (! is_file($path)) {
            $this->error("  Not found file '{$cleanPath}'.");

            return;
        }

        $config    = config(EmailConfig::class);
        $fromEmail = (string) $config->fromEmail; // Old Config may return null.
        $fromName  = (string) $config->fromName;

        if ($fromEmail !== '' && $fromName !== '') {
            $this->write(CLI::color('  Email Setup: ', 'green') . 'Everything is fine.');

            return;
        }

        $content = file_get_contents($path);
        $output  = $content;

        if ($fromEmail === '') {
            $set = $this->prompt('  The required Config\Email::$fromEmail is not set. Do you set now?', ['y', 'n']);

            if ($set === 'y') {
                // Input from email
                $fromEmail = $this->prompt('  What is your email?', null, 'required|valid_email');

                $pattern = '/^    public .*\$fromEmail\s+= \'\';/mu';
                $replace = '    public string $fromEmail  = \'' . $fromEmail . '\';';
                $output  = preg_replace($pattern, $replace, $content);
            }
        }

        if ($fromName === '') {
            $set = $this->prompt('  The required Config\Email::$fromName is not set. Do you set now?', ['y', 'n']);

            if ($set === 'y') {
                $fromName = $this->prompt('  What is your name?', null, 'required');

                $pattern = '/^    public .*\$fromName\s+= \'\';/mu';
                $replace = '    public string $fromName   = \'' . $fromName . '\';';
                $output  = preg_replace($pattern, $replace, $output);
            }
        }

        if (write_file($path, $output)) {
            $this->write(CLI::color('  Updated: ', 'green') . $cleanPath);
        } else {
            $this->error("  Error updating file '{$cleanPath}'.");
        }
    }

    private function runMigrations(): void
    {
        if (
            $this->prompt('  Run `spark migrate --all` now?', ['y', 'n']) === 'n'
        ) {
            return;
        }

        $command = new Migrate(Services::logger(), Services::commands());

        // This is a hack for testing.
        // @TODO Remove CITestStreamFilter and refactor when CI 4.5.0 or later is supported.
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();
        CITestStreamFilter::addErrorFilter();

        $command->run(['all' => null]);

        CITestStreamFilter::removeOutputFilter();
        CITestStreamFilter::removeErrorFilter();

        // Capture the output, and write for testing.
        // @TODO Remove CITestStreamFilter and refactor when CI 4.5.0 or later is supported.
        $output = CITestStreamFilter::$buffer;
        $this->write($output);

        CITestStreamFilter::$buffer = '';
    }
}
