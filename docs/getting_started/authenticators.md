# Authenticators

## Authenticator List

Shield provides the following Authenticators:

- **Session** authenticator provides traditional Email/Password authentication.
  See [Using Session Authenticator](../quick_start_guide/using_session_auth.md)
  for usage.
- **AccessTokens** authenticator provides stateless authentication using Personal Access Tokens.
  See [Protecting an API with Access Tokens](../guides/api_tokens.md) for usage.
- **HmacSha256** authenticator provides stateless authentication using HMAC Keys.
  See [Protecting an API with HMAC Keys](../guides/api_hmac_keys.md) for usage.
- **JWT** authenticator provides stateless authentication using JSON Web Token. To use this,
  you need additional setup. See [JWT Authentication](../addons/jwt.md).
