<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Validation;

use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Config\Auth;

class ValidationRules
{
    private Auth $config;

    /**
     * Auth Table names
     */
    private array $tables;

    public function __construct()
    {
        /** @var Auth $authConfig */
        $authConfig = config('Auth');

        $this->config = $authConfig;
        $this->tables = $this->config->tables;
    }

    public function getRegistrationRules(): array
    {
        helper('setting');

        $setting = setting('Validation.registration');
        if ($setting !== null) {
            return $setting;
        }

        $usernameValidationRules = $this->config->usernameValidationRules;
        $emailValidationRules    = $this->config->emailValidationRules;

        $usernameValidationRules['rules'] = array_merge(
            $usernameValidationRules['rules'],
            [sprintf('is_unique[%s.username]', $this->tables['users'])]
        );
        $emailValidationRules['rules'] = array_merge(
            $emailValidationRules['rules'],
            [sprintf('is_unique[%s.secret]', $this->tables['identities'])]
        );

        $passwordRules            = $this->getPasswordRules();
        $passwordRules['rules'][] = 'strong_password[]';

        return [
            'username'         => $usernameValidationRules,
            'email'            => $emailValidationRules,
            'password'         => $passwordRules,
            'password_confirm' => $this->getPasswordConfirmRules(),
        ];
    }

    public function getLoginRules(): array
    {
        helper('setting');

        return setting('Validation.login') ?? [
            // 'username' => $this->config->usernameValidationRules,
            'email'    => $this->config->emailValidationRules,
            'password' => $this->getPasswordRules(),
        ];
    }

    public function getPasswordRules(): array
    {
        return [
            'label'  => 'Auth.password',
            'rules'  => ['required', Passwords::getMaxLengthRule()],
            'errors' => [
                'max_byte' => 'Auth.errorPasswordTooLongBytes',
            ],
        ];
    }

    public function getPasswordConfirmRules(): array
    {
        return [
            'label' => 'Auth.passwordConfirm',
            'rules' => 'required|matches[password]',
        ];
    }
}
