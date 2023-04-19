<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\JWT\Exceptions;

use CodeIgniter\Shield\Exceptions\ValidationException;
use Exception;

class InvalidTokenException extends ValidationException
{
    public const INVALID_TOKEN      = 1;
    public const EXPIRED_TOKEN      = 2;
    public const BEFORE_VALID_TOKEN = 3;

    public static function forInvalidToken(Exception $e): self
    {
        return new self(lang('Auth.invalidJWT'), self::INVALID_TOKEN, $e);
    }

    public static function forExpiredToken(Exception $e): self
    {
        return new self(lang('Auth.expiredJWT'), self::EXPIRED_TOKEN, $e);
    }

    public static function forBeforeValidToken(Exception $e): self
    {
        return new self(lang('Auth.beforeValidJWT'), self::BEFORE_VALID_TOKEN, $e);
    }
}
