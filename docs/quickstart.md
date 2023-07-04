# Quick Start Guide

Learning any new authentication system can be difficult, especially as they get more flexible and sophisticated. This guide is intended to provide short examples for common actions you'll take when working with Shield. It is not intended to be the exhaustive documentation for each section. That's better handled through the area-specific doc files.

> **Note** The examples assume that you have run the setup script and that you have copies of the `Auth` and `AuthGroups` config files in your application's **app/Config** folder.

- [Quick Start Guide](#quick-start-guide)
  - [Authentication Flow](#authentication-flow)
    - [Configure Config\\Auth](#configure-configauth)
      - [Configure Redirect URLs](#configure-redirect-urls)
      - [Configure Remember-me Functionality](#configure-remember-me-functionality)
      - [Change Access Token Lifetime](#change-access-token-lifetime)
      - [Enable Account Activation via Email](#enable-account-activation-via-email)
      - [Enable Two-Factor Authentication](#enable-two-factor-authentication)
    - [Responding to Magic Link Logins](#responding-to-magic-link-logins)
      - [Session Notification](#session-notification)
      - [Event](#event)
  - [Authorization Flow](#authorization-flow)
    - [Configure Config\\AuthGroups](#configure-configauthgroups)
      - [Change Available Groups](#change-available-groups)
      - [Set the Default Group](#set-the-default-group)
      - [Change Available Permissions](#change-available-permissions)
      - [Assign Permissions to a Group](#assign-permissions-to-a-group)
      - [Assign Permissions to a User](#assign-permissions-to-a-user)
  - [Check If a User Has Permission](#check-if-a-user-has-permission)
    - [Adding a Group To a User](#adding-a-group-to-a-user)
    - [Removing a Group From a User](#removing-a-group-from-a-user)
    - [Checking If User Belongs To a Group](#checking-if-user-belongs-to-a-group)
  - [Managing Users](#managing-users)
    - [Creating Users](#creating-users)
    - [Deleting Users](#deleting-users)
    - [Editing a User](#editing-a-user)

## Authentication Flow

### Configure Config\Auth

#### Configure Redirect URLs

If you need everyone to redirect to a single URL after login/logout/register actions, you can modify the `Config\Auth::$redirects` array in **app/Config/Auth.php**`** to specify the url to redirect to.

By default, a successful login or register attempt will all redirect to `/`, while a logout action
will redirect to a [named route](https://codeigniter.com/user_guide/incoming/routing.html#using-named-routes "See routing docs") `login` or a *URI path* `/login`. You can change the default URLs used within the **`**app/Config/Auth.php** config file:

```php
public array $redirects = [
    'register' => '/',
    'login'    => '/',
    'logout'   => 'login',
];
```

> **Note** This redirect happens after the specified action is complete. In the case of register or login, it might not happen immediately. For example, if you have any Auth Actions specified, they will be redirected when those actions are completed successfully. If no Auth Actions are specified, they will be redirected immediately after registration or login.

#### Configure Remember-me Functionality

Remember-me functionality is enabled by default for the `Session` handler. While this is handled in a secure manner, some sites may want it disabled. You might also want to change how long it remembers a user and doesn't require additional login.

```php
public array $sessionConfig = [
    'field'              => 'user',
    'allowRemembering'   => true,
    'rememberCookieName' => 'remember',
    'rememberLength'     => 30 * DAY,
];
```

#### Change Access Token Lifetime

By default, Access Tokens can be used for 1 year since the last use. This can be easily modified in the `Auth` config file.

```php
public int $unusedTokenLifetime = YEAR;
```

#### Enable Account Activation via Email

> **Note** You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](install.md#initial-setup).

By default, once a user registers they have an active account that can be used. You can enable Shield's built-in, email-based activation flow within the `Auth` config file.

```php
public array $actions = [
    'register' => \CodeIgniter\Shield\Authentication\Actions\EmailActivator::class,
    'login'    => null,
];
```

#### Enable Two-Factor Authentication

> **Note** You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](install.md#initial-setup).

Turned off by default, Shield's Email-based 2FA can be enabled by specifying the class to use in the `Auth` config file.

```php
public array $actions = [
    'register' => null,
    'login'    => \CodeIgniter\Shield\Authentication\Actions\Email2FA::class,
];
```

### Responding to Magic Link Logins

> **Note** You need to configure **app/Config/Email.php** to allow Shield to send emails. See [Installation](install.md#initial-setup).

Magic Link logins allow a user that has forgotten their password to have an email sent with a unique, one-time login link. Once they've logged in you can decide how to respond. In some cases, you might want to redirect them to a special page where they must choose a new password. In other cases, you might simply want to display a one-time message prompting them to go to their account page and choose a new password.

#### Session Notification

You can detect if a user has finished the magic link login by checking for a session value, `magicLogin`. If they have recently completed the flow, it will exist and have a value of `true`.

```php
if (session('magicLogin')) {
    return redirect()->route('set_password');
}
```

This value sticks around in the session for 5 minutes. Once you no longer need to take any actions, you might want to delete the value from the session.

```php
session()->removeTempdata('magicLogin');
```

#### Event

At the same time the above session variable is set, a `magicLogin` [event](https://codeigniter.com/user_guide/extending/events.html) is fired off that you may subscribe to. Note that no data is passed to the event as you can easily grab the current user from the `user()` helper or the `auth()->user()` method.

```php
Events::on('magicLogin', static function () {
    // ...
});
```


## Authorization Flow

### Configure Config\AuthGroups

#### Change Available Groups

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

#### Set the Default Group

When a user registers on your site, they are assigned the group specified at `Config\AuthGroups::$defaultGroup`. Change this to one of the keys in the `$groups` array to update this.

#### Change Available Permissions

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

#### Assign Permissions to a Group

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

#### Assign Permissions to a User

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

> **Note** The example above can also be done through a [controller filter](https://codeigniter.com/user_guide/incoming/filters.html) if you want to apply it to multiple pages of your site.

### Adding a Group To a User

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

### Removing a Group From a User

Groups are removed from a user via the `removeGroup()` method. Multiple groups may be removed at once by passing all of their names into the method.

```php
$user = auth()->user();
$user->removeGroup('admin', 'beta');
```

### Checking If User Belongs To a Group

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

## Managing Users

Since Shield uses a more complex user setup than many other systems, separating [User Identities](concepts.md#user-identities) from the user accounts themselves. This quick overview should help you feel more confident when working with users on a day-to-day basis.

### Creating Users

By default, the only values stored in the users table is the username. The first step is to create the user record with the username. If you don't have a username, be sure to set the value to `null` anyway, so that it passes CodeIgniter's empty data check.

```php
use CodeIgniter\Shield\Entities\User;

// Get the User Provider (UserModel by default)
$users = auth()->getProvider();

$user = new User([
    'username' => 'foo-bar',
    'email'    => 'foo.bar@example.com',
    'password' => 'secret plain text password',
]);
$users->save($user);

// To get the complete user object with ID, we need to get from the database
$user = $users->findById($users->getInsertID());

// Add to default group
$users->addToDefaultGroup($user);
```

### Deleting Users

A user's data can be spread over a few different tables so you might be concerned about how to delete all of the user's data from the system. This is handled automatically at the database level for all information that Shield knows about, through the `onCascade` settings of the table's foreign keys. You can delete a user like any other entity.

```php
// Get the User Provider (UserModel by default)
$users = auth()->getProvider();

$users->delete($user->id, true);
```

> **Note** The User rows use [soft deletes](https://codeigniter.com/user_guide/models/model.html#usesoftdeletes) so they are not actually deleted from the database unless the second parameter is `true`, like above.

### Editing a User

The `UserModel::save()`, `update()` and `insert()` methods have been modified to ensure that an email or password previously set on the `User` entity will be automatically updated in the correct `UserIdentity` record.

```php
// Get the User Provider (UserModel by default)
$users = auth()->getProvider();

$user = $users->findById(123);
$user->fill([
    'username' => 'JoeSmith111',
    'email' => 'joe.smith@example.com',
    'password' => 'secret123'
]);
$users->save($user);
```
