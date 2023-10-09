# Customizing Validation Rules

## Registration

Shield has the following rules for registration by default:

```php
[
    'username' => [
        'label' => 'Auth.username',
        'rules' => [
            'required',
            'max_length[30]',
            'min_length[3]',
            'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
            'is_unique[users.username]',
        ],
    ],
    'email' => [
        'label' => 'Auth.email',
        'rules' => [
            'required',
            'max_length[254]',
            'valid_email',
            'is_unique[auth_identities.secret]',
        ],
    ],
    'password' => [
        'label' => 'Auth.password',
        'rules' => [
                'required',
                'max_byte[72]',
                'strong_password[]',
            ],
        'errors' => [
            'max_byte' => 'Auth.errorPasswordTooLongBytes'
        ]
    ],
    'password_confirm' => [
        'label' => 'Auth.passwordConfirm',
        'rules' => 'required|matches[password]',
    ],
];
```

!!! note

    If you customize the table names, the table names(`users` and `auth_identities`) in the above rules will be automatically changed.
    The rules are implemented in `RegisterController::getValidationRules()`.

If you need a different set of rules for registration, you can specify them in your `Validation` configuration (**app/Config/Validation.php**) like:

```php
//--------------------------------------------------------------------
// Rules For Registration
//--------------------------------------------------------------------
public $registration = [
    'username' => [
        'label' => 'Auth.username',
        'rules' => [
            'required',
            'max_length[30]',
            'min_length[3]',
            'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
            'is_unique[users.username]',
        ],
    ],
    'email' => [
        'label' => 'Auth.email',
        'rules' => [
            'required',
            'max_length[254]',
            'valid_email',
            'is_unique[auth_identities.secret]',
        ],
    ],
    'password' => [
        'label' => 'Auth.password',
        'rules' => 'required|max_byte[72]|strong_password[]',
        'errors' => [
            'max_byte' => 'Auth.errorPasswordTooLongBytes'
        ]
    ],
    'password_confirm' => [
        'label' => 'Auth.passwordConfirm',
        'rules' => 'required|matches[password]',
    ],
];
```

!!! note

    If you customize the table names, set the correct table names in the rules.

## Login

Similar to the process for validation rules in the **Registration** section, you can add rules for the login form to **app/Config/Validation.php** and change the rules.

```php
//--------------------------------------------------------------------
// Rules For Login
//--------------------------------------------------------------------
public $login = [
    // 'username' => [
    //     'label' => 'Auth.username',
    //     'rules' => [
    //         'required',
    //         'max_length[30]',
    //         'min_length[3]',
    //         'regex_match[/\A[a-zA-Z0-9\.]+\z/]',
    //     ],
    // ],
    'email' => [
        'label' => 'Auth.email',
        'rules' => [
            'required',
            'max_length[254]',
            'valid_email'
        ],
    ],
    'password' => [
        'label' => 'Auth.password',
            'rules' => [
                'required',
                'max_byte[72]',
            ],
        'errors' => [
            'max_byte' => 'Auth.errorPasswordTooLongBytes',
        ]
    ],
];
```
