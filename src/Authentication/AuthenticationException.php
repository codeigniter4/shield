<?php

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use Exception;

class AuthenticationException extends Exception
{
    protected $code = 403;

    public static function forUnknownAuthenticator(string $authenticatorAlias)
    {
        return new self(lang('Auth.unknownAuthenticator', [$authenticatorAlias]));
    }

    public static function forUnknownUserProvider()
    {
        return new self(lang('Auth.unknownUserProvider'));
    }

    public static function forInvalidUser()
    {
        return new self(lang('Auth.invalidUser'));
    }

    public static function forNoEntityProvided()
    {
        return new self(lang('Auth.noUserEntity'), 500);
    }

    /**
     * Fires when no minimumPasswordLength has been set
     * in the Auth config file.
     *
     * @return self
     */
    public static function forUnsetPasswordLength()
    {
        return new self(lang('Auth.unsetPasswordLength'), 500);
    }

    /**
     * When the cURL request (to Have I Been Pwned) in PwnedValidator
     * throws a HTTPException it is re-thrown as this one
     *
     * @return self
     */
    public static function forHIBPCurlFail(HTTPException $e)
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
