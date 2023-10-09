# Authenticators

## Authenticator List

Shield provides the following Authenticators:

- **Session** authenticator provides traditional ID/Password authentication.
  It uses username/email/password to authenticate against and stores the user
  information in the session. See [Using Session Authenticator](../quick_start_guide/using_session_auth.md)
  and [Session Authenticator](../references/authentication/session.md) for usage.
- **AccessTokens** authenticator provides stateless authentication using Personal
  Access Tokens passed in the HTTP headers.
  See [Protecting an API with Access Tokens](../guides/api_tokens.md) and
  [Access Token Authenticator](../references/authentication/tokens.md) for usage.
- **HmacSha256** authenticator provides stateless authentication using HMAC Keys.
  See [Protecting an API with HMAC Keys](../guides/api_hmac_keys.md) and
  [HMAC SHA256 Token Authenticator](../references/authentication/hmac.md) for usage.
- **JWT** authenticator provides stateless authentication using JSON Web Token. To use this,
  you need additional setup. See [JWT Authentication](../addons/jwt.md).
