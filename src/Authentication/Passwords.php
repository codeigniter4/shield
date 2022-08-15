<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\Shield\Authentication\Passwords\ValidatorInterface;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Result;

/**
 * Class Passwords
 *
 * Provides a central location to handle password
 * related tasks like hashing, verifying, validating, etc.
 */
class Passwords
{
    protected Auth $config;

    public function __construct(Auth $config)
    {
        $this->config = $config;
    }

    /**
     * Hash a password.
     *
     * @return false|string|null
     */
    public function hash(string $password)
    {
        if ((defined('PASSWORD_ARGON2I') && $this->config->hashAlgorithm === PASSWORD_ARGON2I)
            || (defined('PASSWORD_ARGON2ID') && $this->config->hashAlgorithm === PASSWORD_ARGON2ID)
        ) {
            $hashOptions = [
                'memory_cost' => $this->config->hashMemoryCost,
                'time_cost'   => $this->config->hashTimeCost,
                'threads'     => $this->config->hashThreads,
            ];
        } else {
            $hashOptions = [
                'cost' => $this->config->hashCost,
            ];
        }

        return password_hash(
            base64_encode(
                hash('sha384', $password, true)
            ),
            $this->config->hashAlgorithm,
            $hashOptions
        );
    }

    /**
     * Verifies a password against a previously hashed password.
     *
     * @param string $password The password we're checking
     * @param string $hash     The previously hashed password
     */
    public function verify(string $password, string $hash): bool
    {
        return password_verify(base64_encode(
            hash('sha384', $password, true)
        ), $hash);
    }

    /**
     * Checks to see if a password should be rehashed.
     */
    public function needsRehash(string $hashedPassword): bool
    {
        return password_needs_rehash($hashedPassword, $this->config->hashAlgorithm);
    }

    /**
     * Checks a password against all of the Validators specified
     * in `$passwordValidators` setting in Config\Auth.php.
     *
     * @throws AuthenticationException
     */
    public function check(string $password, ?User $user = null): Result
    {
        if (null === $user) {
            throw AuthenticationException::forNoEntityProvided();
        }

        $password = trim($password);

        if (empty($password)) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.errorPasswordEmpty'),
            ]);
        }

        foreach ($this->config->passwordValidators as $className) {
            /** @var ValidatorInterface $class */
            $class = new $className($this->config);

            $result = $class->check($password, $user);
            if (! $result->isOk()) {
                return $result;
            }
        }

        return new Result([
            'success' => true,
        ]);
    }
}
