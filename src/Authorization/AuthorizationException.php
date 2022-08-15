<?php

declare(strict_types=1);

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
}
