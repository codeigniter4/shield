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

namespace CodeIgniter\Shield\Authentication\Passwords;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Result;

/**
 * Class DictionaryValidator
 *
 * Checks passwords against a list of 65k commonly used passwords
 * that was compiled by InfoSec.
 */
class DictionaryValidator extends BaseValidator implements ValidatorInterface
{
    /**
     * Checks the password against the words in the file and returns false
     * if a match is found. Returns true if no match is found.
     * If true is returned the password will be passed to next validator.
     * If false is returned the validation process will be immediately stopped.
     */
    public function check(string $password, ?User $user = null): Result
    {
        // Loop over our file
        $fp = fopen(__DIR__ . '/_dictionary.txt', 'rb');
        if ($fp) {
            while (($line = fgets($fp, 4096)) !== false) {
                if ($password === trim($line)) {
                    fclose($fp);

                    return new Result([
                        'success'   => false,
                        'reason'    => lang('Auth.errorPasswordCommon'),
                        'extraInfo' => lang('Auth.suggestPasswordCommon'),
                    ]);
                }
            }
        }

        fclose($fp);

        return new Result([
            'success' => true,
        ]);
    }
}
