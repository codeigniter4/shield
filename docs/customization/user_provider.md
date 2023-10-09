# Customizing User Provider

If you want to customize user attributes, you need to create your own
[User Provider](../getting_started/concepts.md#user-providers) class.
The only requirement is that your new class MUST extend the provided `CodeIgniter\Shield\Models\UserModel`.

Shield has a CLI command to quickly create a custom `UserModel` class by running the following
command in the terminal:

```console
php spark shield:model UserModel
```

The class name is optional. If none is provided, the generated class name would be `UserModel`.

After creating the class, set the `$userProvider` property in **app/Config/Auth.php** as follows:

```php
public string $userProvider = \App\Models\UserModel::class;
```
