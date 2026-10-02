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

class GenerateDummyPasswordHash extends BaseCommand
{
    protected $name        = 'shield:generate-dummy-hash';
    protected $description = 'Generate a dummy password hash using the current Auth password settings.';
    protected $usage       = 'shield:generate-dummy-hash';

    public function run(array $params): int
    {
        $hash = service('passwords')->hash(bin2hex(random_bytes(32)));

        if (! is_string($hash)) {
            $this->error('Could not generate a dummy password hash.');

            return EXIT_ERROR;
        }

        $this->write('public string $dummyPasswordHash = \'' . $hash . '\';');

        return EXIT_SUCCESS;
    }
}
