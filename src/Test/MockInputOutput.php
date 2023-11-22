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

namespace CodeIgniter\Shield\Test;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Commands\Utils\InputOutput;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Test\Filters\CITestStreamFilter;
use CodeIgniter\Test\PhpStreamWrapper;

final class MockInputOutput extends InputOutput
{
    private array $inputs  = [];
    private array $outputs = [];

    /**
     * Sets user inputs.
     */
    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
    }

    /**
     * Takes the last output from the output array.
     */
    public function getLastOutput(): string
    {
        return array_pop($this->outputs);
    }

    /**
     * Takes the first output from the output array.
     */
    public function getFirstOutput(): string
    {
        return array_shift($this->outputs);
    }

    /**
     * Returns all outputs.
     */
    public function getOutputs(): string
    {
        return implode('', $this->outputs);
    }

    public function prompt(string $field, $options = null, $validation = null): string
    {
        $input = array_shift($this->inputs);

        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();
        CITestStreamFilter::addErrorFilter();

        PhpStreamWrapper::register();
        PhpStreamWrapper::setContent($input);

        $userInput = CLI::prompt($field, $options, $validation);

        PhpStreamWrapper::restore();

        CITestStreamFilter::removeOutputFilter();
        CITestStreamFilter::removeErrorFilter();

        if ($input !== $userInput) {
            throw new LogicException($input . '!==' . $userInput);
        }

        return $input;
    }

    public function write(
        string $text = '',
        ?string $foreground = null,
        ?string $background = null
    ): void {
        CITestStreamFilter::registration();
        CITestStreamFilter::addOutputFilter();

        CLI::write($text, $foreground, $background);
        $this->outputs[] = CITestStreamFilter::$buffer;

        CITestStreamFilter::removeOutputFilter();
    }

    public function error(string $text, string $foreground = 'light_red', ?string $background = null): void
    {
        CITestStreamFilter::registration();
        CITestStreamFilter::addErrorFilter();

        CLI::error($text, $foreground, $background);
        $this->outputs[] = CITestStreamFilter::$buffer;

        CITestStreamFilter::removeErrorFilter();
    }
}
