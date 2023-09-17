# Shield Documentation

## What is Shield?

Shield is the official authentication and authorization framework for CodeIgniter 4. While
it does provide a base set of tools that are commonly used in websites, it is
designed to be flexible and easily customizable.

### Primary Goals

The primary goals for Shield are:

1. It must be very flexible and allow developers to extend/override almost any part of it.
2. It must have security at its core. It is an auth lib after all.
3. To cover many auth needs right out of the box, but be simple to add additional functionality to.

### Important Features

* Session-based authentication (traditional Email/Password with Remember me)
* Stateless authentication using Personal Access Tokens
* Optional Email verification on account registration
* Optional Email-based Two-Factor Authentication after login
* Magic Login Links when a user forgets their password
* Flexible Groups-based access control (think Roles, but more flexible)
* Users can be granted additional Permissions

### License

Shield is licensed under the MIT License - see the [LICENSE](https://github.com/codeigniter4/shield/blob/develop/LICENSE) file for details.
