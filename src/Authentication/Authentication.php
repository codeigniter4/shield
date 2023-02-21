<?php

namespace Sparks\Shield\Authentication;

use Sparks\Shield\Config\Auth as AuthConfig;
use Sparks\Shield\Interfaces\UserProvider;

class Authentication
{
    /**
     * Instantiated handler objects,
     * stored by handler alias.
     */
    protected array $instances = [];

    protected ?UserProvider $userProvider = null;
    protected AuthConfig $config;

    public function __construct(AuthConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Returns an instance of the specified handler.
     *
     * You can pass 'default' as the handler and it
     * will return an instance of the first handler specified
     * in the Auth config file.
     *
     * @throws AuthenticationException
     *
     * @return AuthenticatorInterface
     */
    public function factory(?string $handler = null)
    {
        // Determine actual handler name
        $handler ??= $this->config->defaultAuthenticator;

        // Return the cached instance if we have it
        if (! empty($this->instances[$handler])) {
            return $this->instances[$handler];
        }

        // Otherwise, try to create a new instance.
        if (! array_key_exists($handler, $this->config->authenticators)) {
            throw AuthenticationException::forUnknownHandler($handler);
        }

        $className = $this->config->authenticators[$handler];

        $this->instances[$handler] = new $className($this->userProvider);

        return $this->instances[$handler];
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
