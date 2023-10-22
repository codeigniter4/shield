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

namespace CodeIgniter\Shield\Commands\Utils;

use CodeIgniter\CLI\CLI;

class InputOutput
{
    /**
     * Asks the user for input.
     *
     * @param string       $field      Output "field" question
     * @param array|string $options    String to a default value, array to a list of options (the first option will be the default value)
     * @param array|string $validation Validation rules
     *
     * @return string The user input
     */
    public function prompt(string $field, $options = null, $validation = null): string
    {
        return CLI::prompt($field, $options, $validation);
    }

    /**
     * Outputs a string to the cli on its own line.
     */
    public function write(
        string $text = '',
        ?string $foreground = null,
        ?string $background = null
    ): void {
        CLI::write($text, $foreground, $background);
    }

    /**
     * Outputs an error to the CLI using STDERR instead of STDOUT
     */
    public function error(string $text, string $foreground = 'light_red', ?string $background = null): void
    {
        CLI::error($text, $foreground, $background);
    }
}
