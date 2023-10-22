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

use CodeIgniter\Shield\Config\Auth as AuthConfig;

class BaseValidator
{
    protected AuthConfig $config;
    protected ?string $error      = null;
    protected ?string $suggestion = null;

    public function __construct(AuthConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the error string that should be displayed to the user.
     */
    public function error(): ?string
    {
        return $this->error;
    }

    /**
     * Returns a suggestion that may be displayed to the user
     * to help them choose a better password. The method is
     * required, but a suggestion is optional. May return
     * null instead.
     */
    public function suggestion(): ?string
    {
        return $this->suggestion;
    }
}
