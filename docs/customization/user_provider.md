# Customizing User Provider

## Creating Your Own UserModel

If you want to customize user attributes, you need to create your own
[User Provider](../getting_started/concepts.md#user-providers) class.
The only requirement is that your new class MUST extend the provided `CodeIgniter\Shield\Models\UserModel`.

Shield has a CLI command to quickly create a custom `UserModel` class by running the following
command in the terminal:

```console
php spark shield:model UserModel
```

The class name is optional. If none is provided, the generated class name would be `UserModel`.

## Configuring to Use Your UserModel

After creating the class, set your model classname to the `$userProvider` property
in **app/Config/Auth.php**:

```php
public string $userProvider = \App\Models\UserModel::class;
```

## Customizing Your UserModel

Customize your model as you like.

If you add attributes, don't forget to add the attributes to the `$allowedFields`
property.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,
            'first_name', // Added
            'last_name',  // Added
        ];
    }
}
```

## Using a Custom User Entity

If you have set a custom `$returnType` in your custom `UserModel`, you may
retrieve the return type using the `UserModel::getReturnType()` method and
easily create a new User Entity using it:

```php
$userEntityClass = $userModel->getReturnType();
$newUser         = new $userEntityClass();
```
