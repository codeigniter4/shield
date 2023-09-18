# Authenticators

## Authenticator List

Shield provides the following Authenticators:

- **Session** authenticator provides traditional Email/Password authentication.
- **AccessTokens** authenticator provides stateless authentication using Personal Access Tokens.
- **JWT** authenticator provides stateless authentication using JSON Web Token. To use this,
  you need additional setup. See [JWT Authentication](./addons/jwt.md).

## Configuration

### AccessTokens Authenticator

#### Change Access Token Lifetime

By default, Access Tokens can be used for 1 year since the last use. This can be easily modified in the the **app/Config/Auth.php** config file.

```php
public int $unusedTokenLifetime = YEAR;
```
