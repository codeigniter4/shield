<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

/**
 * Authenticator Configuration for Token Auth and HMAC Auth
 */
class AuthToken
{
    /**
     * --------------------------------------------------------------------
     * Record Login Attempts for Token and HMAC Authorization
     * --------------------------------------------------------------------
     * Specify which login attempts are recorded in the database.
     *
     * Valid values are:
     * - Auth::RECORD_LOGIN_ATTEMPT_NONE
     * - Auth::RECORD_LOGIN_ATTEMPT_FAILURE
     * - Auth::RECORD_LOGIN_ATTEMPT_ALL
     */
    public int $recordLoginAttempt = Auth::RECORD_LOGIN_ATTEMPT_FAILURE;
}
