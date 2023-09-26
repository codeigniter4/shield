# Customizing Table Names

If you want to change the default table names, you can change the table names
in **app/Config/Auth.php**.

```php
public array $tables = [
    'users'             => 'users',
    'identities'        => 'auth_identities',
    'logins'            => 'auth_logins',
    'token_logins'      => 'auth_token_logins',
    'remember_tokens'   => 'auth_remember_tokens',
    'groups_users'      => 'auth_groups_users',
    'permissions_users' => 'auth_permissions_users',
];
```

Set the table names that you want in the array values.

!!! note

    You must change the table names before running database migrations.
