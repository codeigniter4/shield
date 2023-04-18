<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\JWT\Exceptions;

use CodeIgniter\Shield\Exceptions\ValidationException;
use Exception;

class InvalidTokenException extends ValidationException
{
    public static function forInvalidToken(Exception $e): self
    {
        return new self(lang('Auth.invalidJWT'), 1, $e);
    }

    public static function forExpiredToken(Exception $e): self
    {
        return new self(lang('Auth.expiredJWT'), 2, $e);
    }

    public static function forBeforeValidToken(Exception $e): self
    {
        return new self(lang('Auth.beforeValidJWT'), 3, $e);
    }
}
