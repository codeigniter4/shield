# CodeIgniter Shield

[![Unit Tests](https://github.com/codeigniter4/shield/workflows/PHPUnit/badge.svg)](https://github.com/codeigniter4/shield/actions/workflows/phpunit.yml)
[![Static Analysis](https://github.com/codeigniter4/shield/workflows/PHPStan/badge.svg)](https://github.com/codeigniter4/shield/actions/workflows/phpstan.yml)
[![PHP-CS-Fixer](https://github.com/codeigniter4/shield/actions/workflows/phpcsfixer.yml/badge.svg)](https://github.com/codeigniter4/shield/actions/workflows/phpcsfixer.yml)
[![Rector](https://github.com/codeigniter4/shield/actions/workflows/rector.yml/badge.svg)](https://github.com/codeigniter4/shield/actions/workflows/rector.yml)
[![Psalm](https://github.com/codeigniter4/shield/actions/workflows/psalm.yml/badge.svg)](https://github.com/codeigniter4/shield/actions/workflows/psalm.yml)
[![Architecture](https://github.com/codeigniter4/shield/workflows/Deptrac/badge.svg)](https://github.com/codeigniter4/shield/actions/workflows/deptrac.yml)
[![Coverage Status](https://coveralls.io/repos/github/codeigniter4/shield/badge.svg?branch=develop)](https://coveralls.io/github/codeigniter4/shield?branch=develop)

Shield is the official authentication and authorization framework for CodeIgniter 4.
While it does provide a base set of tools
that are commonly used in websites, it is designed to be flexible and easily customizable.

The primary goals for Shield are:
1. It must be very flexible and allow developers to extend/override almost any part of it.
2. It must have security at its core. It is an auth lib after all.
3. To cover many auth needs right out of the box, but be simple to add additional functionality to.

## Authentication Methods

Shield provides two primary methods **Session-based** and **Access Token**
authentication out of the box.

It also provides **HMAC SHA256 Token** and **JSON Web Token** authentication.

### Session-based

This is your typical email/username/password system you see everywhere. It includes a secure "remember-me" functionality.
This can be used for standard web applications, as well as for single page applications. Includes full controllers and
basic views for all standard functionality, like registration, login, forgot password, etc.

### Access Token

These are much like the access tokens that GitHub uses, where they are unique to a single user, and a single user
can have more than one. This can be used for API authentication of third-party users, and even for allowing
access for a mobile application that you build.

### HMAC SHA256 Token

This is a slightly more complicated improvement on Access Token authentication.
The main advantage with HMAC is the shared Secret Key
is not passed in the request, but is instead used to create a hash signature of the request body.

### JSON Web Token

JWT or JSON Web Token is a compact and self-contained way of securely transmitting
information between parties as a JSON object. It is commonly used for authentication
and authorization purposes in web applications.

## Important Features

* Session-based authentication (traditional ID/Password with Remember-me)
* Stateless authentication using Personal Access Tokens
* Optional Email verification on account registration
* Optional Email-based Two-Factor Authentication after login
* Magic Link Login when a user forgets their password
* Flexible Groups-based access control (think Roles, but more flexible)
* Users can be granted additional Permissions

See the [An Official Auth Library](https://forum.codeigniter.com/showthread.php?tid=82003) for more Info.

## Getting Started

### Prerequisites

Usage of Shield requires the following:

- A [CodeIgniter 4.3.5+](https://github.com/codeigniter4/CodeIgniter4/) based project
- [Composer](https://getcomposer.org/) for package management
- PHP 7.4.3+

### Installation

Installation is done through Composer.

```console
composer require codeigniter4/shield
```

See the <a href="https://codeigniter4.github.io/shield/" target="_blank">docs</a> for more specific instructions on installation and usage recommendations.

## Contributing

Shield does accept and encourage contributions from the community in any shape. It doesn't matter
whether you can code, write documentation, or help find bugs, all contributions are welcome.
See the [CONTRIBUTING.md](CONTRIBUTING.md) file for details.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgements

Every open-source project depends on its contributors to be a success. The following users have
contributed in one manner or another in making Shield:

<a href="https://github.com/codeigniter4/shield/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=codeigniter4/shield" alt="Contributors">
</a>

Made with [contrib.rocks](https://contrib.rocks).

The following articles/sites have been fundamental in shaping the security and best practices used
within this library, in no particular order:

- [Google Cloud: 13 best practices for user account, authentication, and password management, 2021 edition](https://cloud.google.com/blog/products/identity-security/account-authentication-and-password-management-best-practices)
- [NIST Digital Identity Guidelines](https://pages.nist.gov/800-63-3/sp800-63b.html)
- [Implementing Secure User Authentication in PHP Applications with Long-Term Persistence (Login with "Remember Me" Cookies) ](https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence)
- [Password Storage - OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
