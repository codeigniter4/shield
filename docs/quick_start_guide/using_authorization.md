# Using Authorization

## Configuration

### Change Available Groups

The available groups are defined in the **app/Config/AuthGroups.php** config file, under the `$groups` property. Add new entries to the array, or remove existing ones to make them available throughout your application.

```php
public array $groups = [
    'superadmin' => [
        'title'       => 'Super Admin',
        'description' => 'Complete control of the site.',
    ],
    //
];
```

### Set the Default Group

When a user registers on your site, they are assigned the group specified at `Config\AuthGroups::$defaultGroup`. Change this to one of the keys in the `$groups` array to update this.

### Change Available Permissions

The permissions on the site are stored in the `AuthGroups` config file also. Each one is defined by a string that represents a context and a permission, joined with a decimal point.

```php
public array $permissions = [
    'admin.access'        => 'Can access the sites admin area',
    'admin.settings'      => 'Can access the main site settings',
    'users.manage-admins' => 'Can manage other admins',
    'users.create'        => 'Can create new non-admin users',
    'users.edit'          => 'Can edit existing non-admin users',
    'users.delete'        => 'Can delete existing non-admin users',
    'beta.access'         => 'Can access beta-level features',
];
```

### Assign Permissions to a Group

Each group can have its own specific set of permissions. These are defined in `Config\AuthGroups::$matrix`. You can specify each permission by it's full name, or using the context and an asterisk (*) to specify all permissions within that context.

```php
public array $matrix = [
    'superadmin' => [
        'admin.*',
        'users.*',
        'beta.access',
    ],
    //
];
```

## Assign Permissions to a User

Permissions can also be assigned directly to a user, regardless of what groups they belong to. This is done programatically on the `User` Entity.

```php
$user = auth()->user();

$user->addPermission('users.create', 'beta.access');
```

This will add all new permissions. You can also sync permissions so that the user ONLY has the given permissions directly assigned to them. Any not in the provided list are removed from the user.

```php
$user = auth()->user();

$user->syncPermissions('users.create', 'beta.access');
```

## Check If a User Has Permission

When you need to check if a user has a specific permission use the `can()` method on the `User` entity. This method checks permissions within the groups they belong to and permissions directly assigned to the user.

```php
if (! auth()->user()->can('users.create')) {
    return redirect()->back()->with('error', 'You do not have permissions to access that page.');
}
```

!!! note

    The example above can also be done through a [controller filter](https://codeigniter.com/user_guide/incoming/filters.html) if you want to apply it to multiple pages of your site.

## Adding a Group To a User

Groups are assigned to a user via the `addGroup()` method. You can pass multiple groups in and they will all be assigned to the user.

```php
$user = auth()->user();
$user->addGroup('admin', 'beta');
```

This will add all new groups. You can also sync groups so that the user ONLY belongs to the groups directly assigned to them. Any not in the provided list are removed from the user.

```php
$user = auth()->user();
$user->syncGroups('admin', 'beta');
```

## Removing a Group From a User

Groups are removed from a user via the `removeGroup()` method. Multiple groups may be removed at once by passing all of their names into the method.

```php
$user = auth()->user();
$user->removeGroup('admin', 'beta');
```

## Checking If User Belongs To a Group

You can check if a user belongs to a group with the `inGroup()` method.

```php
$user = auth()->user();
if ($user->inGroup('admin')) {
    // do something
}
```

You can pass more than one group to the method and it will return `true` if the user belongs to any of the specified groups.

```php
$user = auth()->user();
if ($user->inGroup('admin', 'beta')) {
    // do something
}
```
