# Managing Users

Since Shield uses a more complex user setup than many other systems, separating [User Identities](../getting_started/concepts.md#user-identities) from the user accounts themselves. This quick overview should help you feel more confident when working with users on a day-to-day basis.

## Managing Users by Code

### Finding a User

You can find an existing user from the User Provider. It returns a `User`
[entity](https://codeigniter.com/user_guide/models/entities.html).

```php
// Get the User Provider (UserModel by default)
$users = auth()->getProvider();

// Find by the user_id
$user = $users->findById(123);
// Find by the user email
$user = $users->findByCredentials(['email' => 'user@example.com']);
```

### Creating Users

By default, the only values stored in the users table is the username.

The first step is to create the user record with the username. If you don't have a username, be sure to set the value to `null` anyway, so that it passes CodeIgniter's empty data check.

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

A user's data can be spread over a few different tables so you might be concerned about how to delete all of the user's data from the system. This is handled automatically at the database level for all information that Shield knows about, through the `onCascade` settings of the table's foreign keys.

You can delete a user like any other entity.

```php
// Get the User Provider (UserModel by default)
$users = auth()->getProvider();

$users->delete($user->id, true);
```

!!! note

    The User rows use [soft deletes](https://codeigniter.com/user_guide/models/model.html#usesoftdeletes) so they are not actually deleted from the database unless the second parameter is `true`, like above.

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

### Listing Users

When displaying a list of users - for example, in the admin panel - we typically use the standard `find*` methods. However, these methods only return basic user information.

If you need additional details like email addresses, groups, or permissions, each piece of information will trigger a separate database query for every user. This happens because user entities lazy-load related data, which can quickly result in a large number of queries.

To optimize this, you can use method scopes like `UserModel::withIdentities()`, `withGroups()`, and `withPermissions()`. These methods preload the related data in a single query (one per each method), drastically reducing the number of database queries and improving performance.

```php
// Get the User Provider (UserModel by default)
$users = auth()->getProvider();

$usersList = $users
    ->withIdentities()
    ->withGroups()
    ->withPermissions()
    ->findAll(10);

// The below code would normally trigger an additional
// DB queries, on every loop iteration, but now it won't

foreach ($usersList as $u) {
    // Because identities are preloaded
    echo $u->email;

    // Because groups are preloaded
    $u->inGroup('admin');

    // Because permissions are preloaded
    $u->hasPermission('users.delete');

    // Because groups and permissions are preloaded
    $u->can('users.delete');
}
```

## Managing Users via CLI

Shield has a CLI command to manage users. You can do the following actions:

```text
create:      Create a new user
activate:    Activate a user
deactivate:  Deactivate a user
changename:  Change user name
changeemail: Change user email
delete:      Delete a user
password:    Change a user password
list:        List users
addgroup:    Add a user to a group
removegroup: Remove a user from a group
```

You can get help on how to use it by running the following command in a terminal:

```console
php spark shield:user --help
```
