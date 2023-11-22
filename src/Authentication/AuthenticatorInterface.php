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

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Result;

interface AuthenticatorInterface
{
    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @throws AuthenticationException
     */
    public function attempt(array $credentials): Result;

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     */
    public function check(array $credentials): Result;

    /**
     * Checks if the user is currently logged in.
     */
    public function loggedIn(): bool;

    /**
     * Logs the given user in.
     * On success this must trigger the "login" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     */
    public function login(User $user): void;

    /**
     * Logs a user in based on their ID.
     * On success this must trigger the "login" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     *
     * @param int|string $userId
     */
    public function loginById($userId): void;

    /**
     * Logs the current user out.
     * On success this must trigger the "logout" Event.
     *
     * @see https://codeigniter4.github.io/CodeIgniter4/extending/authentication.html
     */
    public function logout(): void;

    /**
     * Returns the currently logged in user.
     */
    public function getUser(): ?User;

    /**
     * Updates the user's last active date.
     */
    public function recordActiveDate(): void;
}
