# Events

Shield fires off several events during the lifecycle of the application that your code can tap into.

- [Events](#events)
  - [Responding to Events](#responding-to-events)
    - [Event List](#event-list)
      - [register](#register)
      - [login](#login)
      - [failedLogin](#failedlogin)
      - [logout](#logout)
      - [magicLogin](#magiclogin)
    - [Event Timing](#event-timing)

## Responding to Events

When you want to respond to an event that Shield publishes, you will need to add it to your **app/Config/Events.php**
file. Each of the following events provides a sample for responding that uses a class and method name.
Other methods are available. See the [CodeIgniter 4 User Guide](https://codeigniter.com/user_guide/extending/events.html)
for more information.

### Event List

#### register

Triggered when a new user has registered in the system. It's only argument is the `User` entity itself.

```php
Events::trigger('register', $user);

Events::on('register', 'SomeLibrary::handleRegister');
```

#### login

Fired immediately after a successful login. The only argument is the `User` entity.

```php
Events::trigger('login', $user);

Events::on('login', 'SomeLibrary::handleLogin');
```

#### failedLogin

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

When the magic link login fails, the following array will be provided:

```php
['magicLinkToken' => 'the token value used']
```

#### logout

Fired immediately after a successful logout. The only argument is the `User` entity.

#### magicLogin

Fired when a user has been successfully logged in via a magic link. This event does not have any parameters passed in. The authenticated user can be discovered through the `auth()` helper.

```php
Events::on('magicLogin', function() {
    $user = auth()->user();

    //
})
```

### Event Timing

To learn more about Event timing, please see the list below.

- [Session Authenticator Event and Logging](./session_auth_event_and_logging.md).
