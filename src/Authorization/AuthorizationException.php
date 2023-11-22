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

namespace CodeIgniter\Shield\Authorization;

use CodeIgniter\Shield\Exceptions\RuntimeException;

class AuthorizationException extends RuntimeException
{
    protected $code = 401;

    public static function forUnknownGroup(string $group): self
    {
        return new self(lang('Auth.unknownGroup', [$group]));
    }

    public static function forUnknownPermission(string $permission): self
    {
        return new self(lang('Auth.unknownPermission', [$permission]));
    }

    public static function forUnauthorized(): self
    {
        return new self(lang('Auth.notEnoughPrivilege'));
    }
}
