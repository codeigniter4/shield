# Configuration

## AccessTokens Authenticator

### Access Token Lifetime

By default, Access Tokens can be used for 1 year since the last use. This can be easily modified in the the **app/Config/Auth.php** config file.

```php
public int $unusedTokenLifetime = YEAR;
```
