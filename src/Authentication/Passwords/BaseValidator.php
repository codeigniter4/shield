<?php

namespace Sparks\Shield\Authentication\Passwords;

use Sparks\Shield\Config\Auth as AuthConfig;

class BaseValidator
{
    protected ?AuthConfig $config = null;
    protected ?string $error      = null;
    protected ?string $suggestion = null;

    /**
     * Allows for setting a config file on the Validator.
     *
     * @param AuthConfig $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Returns the error string that should be displayed to the user.
     */
    public function error(): string
    {
        return $this->error;
    }

    /**
     * Returns a suggestion that may be displayed to the user
     * to help them choose a better password. The method is
     * required, but a suggestion is optional. May return
     * an empty string instead.
     */
    public function suggestion(): string
    {
        return $this->suggestion;
    }
}
