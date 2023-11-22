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

use CodeIgniter\Shield\Config\Auth as AuthConfig;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Factory for Authenticators.
 */
class Authentication
{
    /**
     * Instantiated Authenticator objects,
     * stored by Authenticator alias.
     *
     * @var array<string, AuthenticatorInterface> [Authenticator_alias => Authenticator_instance]
     */
    protected array $instances = [];

    protected ?UserModel $userProvider = null;
    protected AuthConfig $config;

    public function __construct(AuthConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Creates and returns the shared instance of the specified Authenticator.
     *
     * @param string|null $alias Authenticator alias. Passing `null` returns the
     *                           default authenticator.
     *
     * @throws AuthenticationException
     */
    public function factory(?string $alias = null): AuthenticatorInterface
    {
        // Determine actual Authenticator alias
        $alias ??= $this->config->defaultAuthenticator;

        // Return the cached instance if we have it
        if (! empty($this->instances[$alias])) {
            return $this->instances[$alias];
        }

        // Otherwise, try to create a new instance.
        if (! array_key_exists($alias, $this->config->authenticators)) {
            throw AuthenticationException::forUnknownAuthenticator($alias);
        }

        $className = $this->config->authenticators[$alias];

        assert($this->userProvider !== null, 'You must set $this->userProvider.');

        $this->instances[$alias] = new $className($this->userProvider);

        return $this->instances[$alias];
    }

    /**
     * Sets the User Provider to use.
     *
     * @return $this
     */
    public function setProvider(UserModel $provider): self
    {
        $this->userProvider = $provider;

        return $this;
    }
}
