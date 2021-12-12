# Events

Shield fires off several events during the lifecycle of the application that your code can tap into. They are as follows.

**didRegister**

Triggered when a new user has registered in the system. It's only argument is the `User` entity itself.

```php
Events::trigger('didRegister', $user);

Events::on('didRegister', 'SomeLibrary::handleRegister');
```

**didLogin**

Fired immediately after a successful login. The only argument is the `User` entity.

```php
Events::trigger('didLogin', $user);

Events::on('didLogin', 'SomeLibrary::handleLogin');
```

**failedLogin**

Triggered when a login attempt fails. It provides an array containing the credentials the user attempted to 
sign in with, with the password removed from the array.

```php
// Original credentials array
$credentials = ['email' => 'foo@example.com', 'password' => 'secret123'];

Events::on('failedLogin', function($credentials) {
    dd($credentials);
});

// Outputs:

['email' => 'foo@example.com'];
```
