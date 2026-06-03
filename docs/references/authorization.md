# Authorization

Authorization happens once a user has been identified through authentication. It is the process of
determining what actions a user is allowed to do within your site.

Shield provides a flexible role-based access control (RBAC) that allows users to belong to multiple groups at once.
Groups can be thought of as traditional roles (admin, moderator, user, etc), but can also group people together
around features, like Beta feature access, or used to provide discrete groups of users within a forum, etc.

## Defining Available Groups

Groups are defined within the `Shield\Config\AuthGroups` config class.

```php
public array $groups = [
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
public string $defaultGroup = 'user';
```

## Defining Available Permissions

Permissions that can be assigned directly to users must be added to the `AuthGroups` config file.
A permission is a string, usually written with dot-separated segments like `users.create` or
`forum.posts.create`. Single-segment permissions are also allowed. Each permission can have a description for display within UIs if needed.

```php
public array $permissions = [
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
config file, under the `$matrix` property.

!!! note

    This defines **group-level permissions**.

The matrix is an associative array with the group name as the key,
and an array of permissions that should be applied to that group.

```php
public array $matrix = [
    'admin' => [
        'admin.access',
        'users.create', 'users.edit', 'users.delete',
        'beta.access'
    ],
];
```

You can use `*` as a wildcard segment to allow permissions under a scope. A wildcard matches one full segment.
When the wildcard is trailing, it grants descendant permissions only.
The first segment cannot be `*`, and a standalone `*` permission does not grant all permissions.

```php
public array $matrix = [
    'superadmin' => ['admin.*', 'users.*', 'beta.*'],
];
```

For example, `forum.posts.*` matches `forum.posts.create` and `forum.posts.comments.delete`, but not
`forum.posts`.
Wildcards can also appear between segments: `forum.*.create` matches `forum.posts.create` and
`forum.comments.create`, but does not match `forum.create` or `forum.posts.comments.create`.

Exact child permissions do not grant their parent permission. For example, `forum.posts.create` does not grant
`forum.posts`.

Wildcard matching is used by `$user->can()` and `$group->can()` for both user-level and group-level permissions.

!!! warning

    Wildcard permissions can grant access to future child permissions added under the same scope. Use broad
    wildcards like `admin.*` carefully, and prefer literal permissions for highly sensitive access.

## Authorizing Users

The `Authorizable` trait on the `User` entity provides the following methods to authorize your users.

#### can()

Allows you to check if a user has one or more permissions. The permission string(s) should be passed as the argument(s). Returns
boolean `true`/`false`. Will check the user's direct permissions (**user-level permissions**) first, and then check against all of the user's groups
permissions (**group-level permissions**) to determine if they are allowed. Wildcard permissions are supported for both
user-level and group-level permissions.

```php
if ($user->can('users.create')) {
    //
}

// If multiple permissions are specified, true is returned if the user has any of them.
if ($user->can('users.create', 'users.edit')) {
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

!!! note

    This method checks only **user-level permissions**, and does not check
    group-level permissions. If you want to check if the user can do something,
    use the `$user->can()` method instead.

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

!!! note

    If you set more than one filter to a route, you need to enable
    [Multiple Filters](https://codeigniter.com/user_guide/incoming/routing.html#multiple-filters).

## Managing User Permissions

Permissions can be granted on a user level as well as on a group level. Any user-level permissions granted will
override the group, so it's possible that a user can perform an action that their groups cannot.

#### addPermission()

Adds one or more **user-level** permissions to the user. If a permission doesn't exist, a `CodeIgniter\Shield\Authorization\AuthorizationException`
is thrown.

```php
$user->addPermission('users.create', 'users.edit');
```

Wildcard permissions can also be assigned to a user, but they must be listed in `Config\AuthGroups::$permissions`
before they can be assigned.

```php
public array $permissions = [
    'forum.posts.*' => 'Can manage forum posts',
];

$user->addPermission('forum.posts.*');
```

#### removePermission()

Removes one or more **user-level** permissions from a user. If a permission doesn't exist, a `CodeIgniter\Shield\Authorization\AuthorizationException`
is thrown.

```php
$user->removePermission('users.delete');
```

#### syncPermissions()

Updates the user's **user-level** permissions to only include the permissions in the given list. Any existing permissions on that user
not in this list will be removed.

```php
$user->syncPermissions('admin.access', 'beta.access');
```

#### getPermissions()

Returns all **user-level** permissions this user has assigned directly to them.

```php
$user->getPermissions();
```

!!! note

    This method does not return **group-level permissions**.

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

All users have an `active` flag. This is only used when the [`EmailActivation` action](./authentication/auth_actions.md), or a custom action used to activate a user, is enabled.

### Checking Activation Status

You can determine if a user has been activated with the `isActivated()` method.

```php
if ($user->isActivated()) {
    //
}
```

!!! note

    If no activator is specified in the `Auth` config file, `actions['register']` property, then this will always return `true`.
    If a conditional activator does not apply during registration, the newly registered user is activated immediately.

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
