# Adding Attributes to Users

If you need to add new attributes like phone numbers, employee or school IDs, etc.
to users, one way is to add columns to `users` table.

## Create Migration File

Create a migration file to add new columns.

You can easily create a file for it with the `spark` command:
```console
php spark make:migration AddMobileNumberToUsers
```

And write code to add/drop columns.

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;

class AddMobileNumberToUsers extends Migration
{
    /**
     * @var string[]
     */
    private array $tables;

    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);

        /** @var \Config\Auth $authConfig */
        $authConfig   = config('Auth');
        $this->tables = $authConfig->tables;
    }

    public function up()
    {
        $fields = [
            'mobile_number' => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
        ];
        $this->forge->addColumn($this->tables['users'], $fields);
    }

    public function down()
    {
        $fields = [
            'mobile_number',
        ];
        $this->forge->dropColumn($this->tables['users'], $fields);
    }
}
```

## Run Migrations

Run the migration file:

```console
php spark migrate
```

And check the `users` table:

```console
php spark db:table users
```

## Create UserModel

See [Customizing User Provider](./user_provider.md).

## Update Validation Rules

You need to update the [validation rules](./validation_rules.md) for registration.

If you do not add the validation rules for the new fields, the new field data will
not be saved to the database.

Add the `$registration` property with the all validation rules for registration
in **app/Config/Validation.php**:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // ...

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------
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
        'mobile_number' => [
            'label' => 'Mobile Number',
            'rules' => [
                'max_length[20]',
                'min_length[10]',
                'regex_match[/\A[0-9]+\z/]',
                'is_unique[users.mobile_number]',
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
                'max_byte' => 'Auth.errorPasswordTooLongBytes',
            ]
        ],
        'password_confirm' => [
            'label' => 'Auth.passwordConfirm',
            'rules' => 'required|matches[password]',
        ],
    ];
}
```

## Customize Register View

1. Change the `register` view file in the **app/Config/Auth.php** file.

    ```php
    public array $views = [
        // ...
        'register'                   => '\App\Views\Shield\register',
        // ...
    ];
    ```

2. Copy file **vendor/codeigniter4/shield/src/Views/register.php** to **app/Views/Shield/register.php**.
3. Customize the registration form to add the new fields.

    ```php
    <!-- Mobile Number -->
    <div class="form-floating mb-2">
        <input type="tel" class="form-control" id="floatingMobileNumberInput" name="mobile_number" autocomplete="tel" placeholder="Mobile Number (without hyphen)" value="<?= old('mobile_number') ?>">
        <label for="floatingMobileNumberInput">Mobile Number (without hyphen)</label>
    </div>
    ```
