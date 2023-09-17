# Shield Documentation

## What is Shield?

Shield is an authentication and authorization framework for CodeIgniter 4. While
it does provide a base set of tools that are commonly used in websites, it is
designed to be flexible and easily customizable.

### Primary Goals

The primary goals for Shield are:

1. It must be very flexible and allow developers to extend/override almost any part of it.
2. It must have security at its core. It is an auth lib after all.
3. To cover many auth needs right out of the box, but be simple to add additional functionality to.

### Important Features

* Session-based authentication (traditional email/password with remember me)
* Stateless authentication using Personal Access Tokens
* Optional Email verification on account registration
* Optional Email-based Two-Factor Authentication after login
* Magic Login Links when a user forgets their password
* Flexible groups-based access control (think roles, but more flexible)
* Users can be granted additional permissions

### License

Shield is licensed under the MIT License - see the [LICENSE](https://github.com/codeigniter4/shield/blob/develop/LICENSE) file for details.

## Getting Started

* [Installation Guide](install.md)
* [Concepts You Need To Know](concepts.md)
* [Quick Start Guide](quickstart.md)
* [Authentication](authentication.md)
* [Authorization](authorization.md)
* [Auth Actions](auth_actions.md)
* [Events](events.md)
* [Testing](testing.md)
* [Customization](customization.md)
* [Forcing Password Reset](forcing_password_reset.md)
* [Banning Users](banning_users.md)

## Guides

* [Protecting an API with Access Tokens](guides/api_tokens.md)
* [Mobile Authentication with Access Tokens](guides/mobile_apps.md)
* [How to Strengthen the Password](guides/strengthen_password.md)

## Addons

* [JWT Authentication](addons/jwt.md)
