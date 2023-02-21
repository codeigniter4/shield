<?php

namespace Sparks\Shield\Authentication\Passwords;

use CodeIgniter\Entity\Entity;
use Sparks\Shield\Result;

/**
 * Interface ValidatorInterface
 *
 * Forms the
 */
interface ValidatorInterface
{
    /**
     * Checks the password and returns true/false
     * if it passes muster. Must return either true/false.
     * True means the password passes this test and
     * the password will be passed to any remaining validators.
     * False will immediately stop validation process
     *
     * @param Entity $user
     */
    public function check(string $password, ?Entity $user = null): Result;

    /**
     * Returns the error string that should be displayed to the user.
     */
    public function error(): string;

    /**
     * Returns a suggestion that may be displayed to the user
     * to help them choose a better password. The method is
     * required, but a suggestion is optional. May return
     * null instead.
     */
    public function suggestion(): string;
}
