# Configuration

## Config files

Shield has a lot of Config items. Change the default values as needed.

If you have completed the setup according to this documentation, you will have
the following configuration files:

- **app/Config/Auth.php**
- **app/Config/AuthGroups.php** - For [Authorization](../references/authorization.md)
- **app/Config/AuthToken.php** - For [AccessTokens](../references/authentication/tokens.md#configuration) and [HmacSha256](../references/authentication/hmac.md#configuration) Authentication
- **app/Config/AuthJWT.php** - For [JWT Authentication](../addons/jwt.md#configuration)

Note that you do not need to have configuration files for features you do not use.
