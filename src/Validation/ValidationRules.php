<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Validation;

use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Config\Auth;

class ValidationRules
{
    /**
     * Auth Table names
     */
    private array $tables;

    public function __construct()
    {
        /** @var Auth $authConfig */
        $authConfig   = config('Auth');
        $this->tables = $authConfig->tables;
    }

    public function getRegistrationRules(): array
    {
        $config = config('Auth');

        $usernameValidationRules = $config->usernameValidationRules;
        $emailValidationRules    = $config->emailValidationRules;

        $usernameValidationRules['rules'] = array_merge(
            $usernameValidationRules['rules'],
            [sprintf('is_unique[%s.username]', $this->tables['users'])]
        );
        $emailValidationRules['rules'] = array_merge(
            $emailValidationRules['rules'],
            [sprintf('is_unique[%s.secret]', $this->tables['identities'])]
        );

        helper('setting');

        return setting('Validation.registration') ?? [
            'username' => $usernameValidationRules,
            'email'    => $emailValidationRules,
            'password' => [
                'label'  => 'Auth.password',
                'rules'  => 'required|' . Passwords::getMaxLengthRule() . '|strong_password[]',
                'errors' => [
                    'max_byte' => 'Auth.errorPasswordTooLongBytes',
                ],
            ],
            'password_confirm' => [
                'label' => 'Auth.passwordConfirm',
                'rules' => 'required|matches[password]',
            ],
        ];
    }
}
