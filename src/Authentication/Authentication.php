<?php

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\Shield\Config\Auth as AuthConfig;
use CodeIgniter\Shield\Interfaces\UserProvider;

class Authentication
{
    /**
     * Instantiated Authenticator objects,
     * stored by Authenticator alias.
     *
     * @var array<string, AuthenticatorInterface>
     */
    protected array $instances = [];

    protected ?UserProvider $userProvider = null;
    protected AuthConfig $config;

    public function __construct(AuthConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Returns an instance of the specified Authenticator.
     *
     * You can pass 'default' as the Authenticator and it
     * will return an instance of the first Authenticator specified
     * in the Auth config file.
     *
     * @throws AuthenticationException
     *
     * @return AuthenticatorInterface
     */
    public function factory(?string $authenticatorAlias = null)
    {
        // Determine actual Authenticator alias
        $authenticatorAlias ??= $this->config->defaultAuthenticator;

        // Return the cached instance if we have it
        if (! empty($this->instances[$authenticatorAlias])) {
            return $this->instances[$authenticatorAlias];
        }

        // Otherwise, try to create a new instance.
        if (! array_key_exists($authenticatorAlias, $this->config->authenticators)) {
            throw AuthenticationException::forUnknownAuthenticator($authenticatorAlias);
        }

        $className = $this->config->authenticators[$authenticatorAlias];

        assert($this->userProvider !== null, '$userProvider must be set.');

        $this->instances[$authenticatorAlias] = new $className($this->userProvider);

        return $this->instances[$authenticatorAlias];
    }

    /**
     * Sets the User provider to use
     *
     * @return $this
     */
    public function setProvider(UserProvider $provider)
    {
        $this->userProvider = $provider;

        return $this;
    }
}
