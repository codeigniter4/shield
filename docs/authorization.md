# Authorization

- [Authorization](#authorization)
  - [Defining Available Groups](#defining-available-groups)
    - [Default User Group](#default-user-group)
  - [Defining Available Permissions](#defining-available-permissions)
  - [Assigning Permissions to Groups](#assigning-permissions-to-groups)
  - [Authorizing Users](#authorizing-users)
    - [can()](#can)
    - [inGroup()](#ingroup)
    - [hasPermission()](#haspermission)
    - [Authorizing via Routes](#authorizing-via-routes)
  - [Managing User Permissions](#managing-user-permissions)
    - [addPermission()](#addpermission)
    - [removePermission()](#removepermission)
    - [syncPermissions()](#syncpermissions)
    - [getPermissions()](#getpermissions)
  - [Managing User Groups](#managing-user-groups)
    - [addGroup()](#addgroup)
    - [removeGroup()](#removegroup)
    - [syncGroups()](#syncgroups)
    - [getGroups()](#getgroups)
  - [User Activation](#user-activation)
    - [Checking Activation Status](#checking-activation-status)
    - [Activating a User](#activating-a-user)
    - [Deactivating a User](#deactivating-a-user)

Authorization happens once a user has been identified through authentication. It is the process of
determining what actions a user is allowed to do within your site.

Shield provides a flexible role-based access control (RBAC) that allows users to belong to multiple groups at once.
Groups can be thought of as traditional roles (admin, moderator, user, etc), but can also group people together
around features, like Beta feature access, or used to provide discrete groups of users within a forum, etc.

## Defining Available Groups

Groups are defined within the `Shield\Config\AuthGroups` config class.

```php

public $groups = [
    'superadmin' => [
        'title'       => 'Super Admin',
        'description' => 'Optional description of the group.',
    ],
];
```

The key of the `$groups` array is the common term of the group. This is what you would call when referencing the
group elsewhere, like checking if `$user->inGroup('superadmin')`. By default, the following groups are available:
`superadmin`, `admin`, `developer`, `user`, and `beta`.

### Default User Group

When a user is first registered on the site, they are assigned to a default user group. This group is defined in
`Config\AuthGroups::$defaultGroup`, and must match the name of one of the defined groups.

```php
public $defaultGroup = 'users';
```

## Defining Available Permissions

All permissions must be added to the `AuthGroups` config file, also. A permission is simply a string consisting of
a scope and action, like `users.create`. The scope would be `users` and the action would be `create`. Each permission
can have a description for display within UIs if needed.

```php
public $permissions = [
    'admin.access'        => 'Can access the sites admin area',
    'admin.settings'      => 'Can access the main site settings',
    'users.manage-admins' => 'Can manage other admins',
    'users.create'        => 'Can create new non-admin users',
    'users.edit'          => 'Can edit existing non-admin users',
    'users.delete'        => 'Can delete existing non-admin users',
    'beta.access'         => 'Can access beta-level features'
];
```

## Assigning Permissions to Groups

In order to grant any permissions to a group, they must have the permission assigned to the group, within the `AuthGroups`
config file, under the `$matrix` property. The matrix is an associative array with the group name as the key,
and an array of permissions that should be applied to that group.

```php
public $matrix = [
    'admin' => ['admin.access', 'users.create', 'users.edit', 'users.delete', 'beta.access'],
];
```

You can use a wildcard within a scope to allow all actions within that scope, by using a `*` in place of the action.

```php
public $matrix = [
    'superadmin' => ['admin.*', 'users.*', 'beta.*'],
];
```

## Authorizing Users

The `Authorizable` trait on the `User` entity provides the following methods to authorize your users.

#### can()

Allows you to check if a user is permitted to do a specific action. The only argument is the permission string. Returns
boolean `true`/`false`. Will check the user's direct permissions first, and then check against all of the user's groups
permissions to determine if they are allowed.

```php
if ($user->can('users.create')) {
    //
}
```

#### inGroup()

Checks if the user is in one of the groups passed in. Returns boolean `true`/`false`.

```php
if (! $user->inGroup('superadmin', 'admin')) {
    //
}
```

#### hasPermission()

Checks to see if the user has the permission set directly on themselves. This disregards any groups they are part of.

```php
if (! $user->hasPermission('users.create')) {
    //
}
```

#### Authorizing via Routes

You can restrict access to a route or route group through a
[Controller Filter](https://codeigniter.com/user_guide/incoming/filters.html).

One is provided for restricting via groups the user belongs to, the other
is for permission they need. The filters are automatically registered with the
system under the `group` and `permission` aliases, respectively.

You can set the filters within **app/Config/Routes.php**:

```php
$routes->group('admin', ['filter' => 'group:admin,superadmin'], static function ($routes) {
    $routes->group(
        '',
        ['filter' => ['group:admin,superadmin', 'permission:users.manage']],
        static function ($routes) {
            $routes->resource('users');
        }
    );
});
```

Note that the options (`filter`) passed to the outer `group()` are not merged with the inner `group()` options.

> **Note** If you set more than one filter to a route, you need to enable
> [Multiple Filters](https://codeigniter.com/user_guide/incoming/routing.html#multiple-filters).

## Managing User Permissions

Permissions can be granted on a user level as well as on a group level. Any user-level permissions granted will
override the group, so it's possible that a user can perform an action that their groups cannot.

#### addPermission()

Adds one or more permissions to the user. If a permission doesn't exist, a `CodeIgniter\Shield\Authorization\AuthorizationException`
is thrown.

```php
$user->addPermission('users.create', 'users.edit');
```

#### removePermission()

Removes one or more permissions from a user. If a permission doesn't exist, a `CodeIgniter\Shield\Authorization\AuthorizationException`
is thrown.

```php
$user->removePermission('users.delete');
```

#### syncPermissions()

Updates the user's permissions to only include the permissions in the given list. Any existing permissions on that user
not in this list will be removed.

```php
$user->syncPermissions('admin.access', 'beta.access');
```

#### getPermissions()

Returns all permissions this user has assigned directly to them.

```php
$user->getPermissions();
```

## Managing User Groups

#### addGroup()

Adds one or more groups to a user. If a group doesn't exist, a `CodeIgniter\Shield\Authorization\AuthorizationException`
is thrown.

```php
$user->addGroup('admin', 'beta');
```

#### removeGroup()

Removes one or more groups from a user. If a group doesn't exist, a `CodeIgniter\Shield\Authorization\AuthorizationException`
is thrown.

```php
$user->removeGroup('admin', 'beta');
```

#### syncGroups()

Updates the user's groups to only include the groups in the given list. Any existing groups on that user
not in this list will be removed.

```php
$user->syncGroups('admin', 'beta');
```

#### getGroups()

Returns all groups this user is a part of.

```php
$user->getGroups();
```

## User Activation

All users have an `active` flag. This is only used when the [`EmailActivation` action](./auth_actions.md), or a custom action used to activate a user, is enabled.

### Checking Activation Status

You can determine if a user has been activated with the `isActivated()` method.

```php
if ($user->isActivated()) {
    //
}
```

> **Note** If no activator is specified in the `Auth` config file, `actions['register']` property, then this will always return `true`.

You can check if a user has not been activated yet via the `isNotActivated()` method.

```php
if ($user->isNotActivated()) {
    //
}
```

### Activating a User

Users are automatically activated within the `EmailActivator` action. They can be manually activated via the `activate()` method on the `User` entity.

```php
$user->activate();
```

### Deactivating a User

Users can be manually deactivated via the `deactivate()` method on the `User` entity.

```php
$user->deactivate();
```
