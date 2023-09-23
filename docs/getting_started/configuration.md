# Configuration

## Config files

Shield has a lot of Config items. Change the default values as needed.

If you have completed the setup according to this documentation, you will have
the following configuration files:

- **app/Config/Auth.php**
- **app/Config/AuthGroups.php** - For Authorization
- **app/Config/AuthToken.php** - For AccessTokens and HmacSha256 Authentication
- **app/Config/AuthJWT.php** - For JWT Authentication

Note that you do not need to have configuration files for features you do not use.

This section describes the major Config items that are not described elsewhere.

## AccessTokens Authenticator

### Access Token Lifetime

By default, Access Tokens can be used for 1 year since the last use. This can be easily modified in the **app/Config/AuthToken.php** config file.

```php
public int $unusedTokenLifetime = YEAR;
```
