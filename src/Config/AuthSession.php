<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Config for Session Authenticator
 */
class AuthSession extends BaseConfig
{
    /**
     * The validation rules for username
     *
     * @var string[]
     */
    public array $usernameValidationRules = [
        'required',
        'max_length[30]',
        'min_length[3]',
        'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
    ];

    /**
     * The validation rules for email
     *
     * @var string[]
     */
    public array $emailValidationRules = [
        'required',
        'max_length[254]',
        'valid_email',
    ];
}
