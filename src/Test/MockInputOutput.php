<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Test;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CodeIgniter;
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

        if (version_compare(CodeIgniter::CI_VERSION, '4.3.0', '>=')) {
            CITestStreamFilter::registration();
            CITestStreamFilter::addOutputFilter();

            PhpStreamWrapper::register();
            PhpStreamWrapper::setContent($input);

            $userInput = CLI::prompt($field, $options, $validation);

            PhpStreamWrapper::restore();

            CITestStreamFilter::removeOutputFilter();
            CITestStreamFilter::removeErrorFilter();

            if ($input !== $userInput) {
                throw new LogicException($input . '!==' . $userInput);
            }
        }

        return $input;
    }

    public function write(
        string $text = '',
        ?string $foreground = null,
        ?string $background = null
    ): void {
        if (version_compare(CodeIgniter::CI_VERSION, '4.3.0', '>=')) {
            CITestStreamFilter::registration();
            CITestStreamFilter::addOutputFilter();
        } else {
            CITestStreamFilter::$buffer = '';

            $streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        }

        CLI::write($text, $foreground, $background);
        $this->outputs[] = CITestStreamFilter::$buffer;

        if (version_compare(CodeIgniter::CI_VERSION, '4.3.0', '>=')) {
            CITestStreamFilter::removeOutputFilter();
            CITestStreamFilter::removeErrorFilter();
        } else {
            stream_filter_remove($streamFilter);
        }
    }
}
