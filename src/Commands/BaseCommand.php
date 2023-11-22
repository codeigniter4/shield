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

use CodeIgniter\CLI\BaseCommand as FrameworkBaseCommand;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Shield\Commands\Utils\InputOutput;
use Psr\Log\LoggerInterface;

abstract class BaseCommand extends FrameworkBaseCommand
{
    protected static ?InputOutput $io = null;

    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Shield';

    public function __construct(LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);

        $this->ensureInputOutput();
    }

    /**
     * Asks the user for input.
     *
     * @param string       $field      Output "field" question
     * @param array|string $options    String to a default value, array to a list of options (the first option will be the default value)
     * @param array|string $validation Validation rules
     *
     * @return string The user input
     */
    protected function prompt(string $field, $options = null, $validation = null): string
    {
        return self::$io->prompt($field, $options, $validation);
    }

    /**
     * Outputs a string to the cli on its own line.
     */
    protected function write(
        string $text = '',
        ?string $foreground = null,
        ?string $background = null
    ): void {
        self::$io->write($text, $foreground, $background);
    }

    /**
     * Outputs an error to the CLI using STDERR instead of STDOUT
     */
    protected function error(
        string $text,
        string $foreground = 'light_red',
        ?string $background = null
    ): void {
        self::$io->error($text, $foreground, $background);
    }

    protected function ensureInputOutput(): void
    {
        if (self::$io === null) {
            self::$io = new InputOutput();
        }
    }

    /**
     * @internal Testing purpose only
     */
    public static function setInputOutput(InputOutput $io): void
    {
        self::$io = $io;
    }

    /**
     * @internal Testing purpose only
     */
    public static function resetInputOutput(): void
    {
        self::$io = null;
    }
}
