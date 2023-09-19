# Banning Users

Shield provides a way to ban users from your application. This is useful if you need to prevent a user from logging in, or logging them out in the event that they breach your terms of service.

### Check if a User is Banned

You can check if a user is banned using `isBanned()` method on the `User` entity. The method returns a boolean `true`/`false`.

```php
if ($user->isBanned()) {
    //...
}
```

### Banning a User

To ban a user from the application, the `ban(?string $message = null)` method can be called on the `User` entity. The method takes an optional string as a parameter. The string acts as the reason for the ban.

```php
// banning a user without passing a message
$user->ban();
// banning a user with a message and reason for the ban passed.
$user->ban('Your reason for banning the user here');
```

### Unbanning a User

Unbanning a user can be done using the `unBan()` method on the `User` entity. This method will also reset the `status_message` property.

```php
$user->unBan();
```

### Getting the Reason for Ban

The reason for the ban can be obtained user the `getBanMessage()` method on the `User` entity.

```php
$user->getBanMessage();
```
