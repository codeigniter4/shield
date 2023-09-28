# Managing Users

Since Shield uses a more complex user setup than many other systems, separating [User Identities](../getting_started/concepts.md#user-identities) from the user accounts themselves. This quick overview should help you feel more confident when working with users on a day-to-day basis.

## Managing Users by Code

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
