# Authorization

Shield provides a flexible role-based access control that allows users to belong to multiple groups at once. 
Groups can be thought of as traditional roles (admin, moderator, user, etc), but can also group people together
around features, like Beta access, or used to provide discrete groups of users within a forum, etc. 

## Defining Available Groups

Roles are defined within the `Shield\Config\AuthGroups` config class.

```php

public $groups = [
    'superadmin' => [
        'title' => 'Super Admin',
        'description' => 'Optional description of the group.',
    ],
];
```

The key of the `$groups` array is the common term of the role. This is what you would call when referencing the 
role elsewhere, like checking if `$user->inGroup('superadmin')`. By default, the following groups are available: 
`superadmin`, `admin`, `developer`, `user`, and `beta`.

## Defining Available Permissions

All permissions must be added to the `AuthGroups` config file, also. A permission is simply a string consisting of
a scope and action, like `users.create`. The scope would be `users` and the action would be `create`. Each permission
can have a description for display within UIs if needed. 

```php
public $permissions = [
    'admin.access' => 'Can access the sites admin area',
    'admin.settings' => 'Can access the main site settings',
    'users.manage-admins' => 'Can manage other admins',
    'users.create' => 'Can create new non-admin users',
    'users.edit' => 'Can edit existing non-admin users',
    'users.delete' => 'Can delete existing non-admin users',
    'beta.access' => 'Can access beta-level features'
];
```

## Assigning Permissions to Roles

In order to grant any permissions to a group, they must have the permission assigned to the role, within the `AuthGroups`
config file, under the `$matrix` property. The matrix is an associative array with the role name as the key, and 
and array of permissions that should be applied to that role. 

```php
public $matrix = [
    'admin' => ['admin.access', 'users.create', 'users.edit', 'users.delete', 'beta.access'],
];
```

You can use a wildcard within a scope to allow all actions within that scope, by using a '*' in place of the action. 

```php
public $matrix = [
    'superadmin' => ['admin.*', 'users.*', 'beta.*'],
];
```

## Authorizing Users

When the `Authorization` trait is applied to the user model, it provides the following methods to authorize your users. 

**can()**

Allows you to check if a user is permitted to do a specific action. The only argument is the permission string. Returns 
boolean `true`/`false`. Will check the user's direct permissions first, and then check against all of the user's groups
permissions to determine if they are allowed.

```php
if ($user->can('users.create')) {
    // 
}
```

**inGroup()**

Checks if the user is in one of the groups passed in. Returns boolean `true`/`false`.

```php
if (! $user->inGroup('superadmin', 'admin')) {
    //
}
```

## Managing User Permissions

Permissions can be granted on a user level as well as on a group level. Any user-level permissions granted will 
override the group, so it's possible that a user can perform an action that their groups cannot.

None of the changes are saved on the User entity until you `save()` with the `UserModel`.

**addPermission()**

Adds one or more permissions to the user. If a permission doesn't exist, a `Sparks\Shield\Authorization\AuthorizationException` 
is thrown. 

```php
$user->addPermission('users.create', 'users.edit');
```

**removePermission()**

Removes one or more permissions from a user. If a permission doesn't exist, a `Sparks\Shield\Authorization\AuthorizationException`
is thrown. 

```php
$user->removePermission('users.delete');
```

## Managing User Groups

**addGroup()**

Adds one or more groups to a user. If a group doesn't exist, a `Sparks\Shield\Authorization\AuthorizationException`
is thrown. 

```php
$user->addGroup('admin', 'beta');
```

**removeGroup()**

Removes one or more groups from a user. If a group doesn't exist, a `Sparks\Shield\Authorization\AuthorizationException`
is thrown.

```php
$user->removeGroup('admin', 'beta');
```
