<?php

declare(strict_types=1);

/**
 * This file is part of StrongPasswordValidationTest
 */

namespace Tests\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * An extendable controller to provide a RESTful API for a resource.
 */
class Users extends ResourceController
{
    public function signup()
    {
        $rules = $this->getValidationRules();

        if (! $this->validate($rules)) {
            return $this->respond(['errors' => $this->validator->getErrors()]);
        }

        return $this->respond(['errors' => '']);
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return string[]
     */
    protected function getValidationRules(): array
    {
        $registrationUsernameRules = array_merge(
            config('AuthSession')->usernameValidationRules,
            ['is_unique[users.username]']
        );
        $registrationEmailRules = array_merge(
            config('AuthSession')->emailValidationRules,
            ['is_unique[auth_identities.secret]']
        );

        return setting('Validation.registration') ?? [
            'username' => [
                'label' => 'Auth.username',
                'rules' => $registrationUsernameRules,
            ],
            'email' => [
                'label' => 'Auth.email',
                'rules' => $registrationEmailRules,
            ],
            'password' => [
                'label' => 'Auth.password',
                'rules' => 'required|strong_password',
            ],
            'password_confirm' => [
                'label' => 'Auth.passwordConfirm',
                'rules' => 'required|matches[password]',
            ],
        ];
    }
}
