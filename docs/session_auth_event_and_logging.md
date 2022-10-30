# Session Authenticator Event and Logging

The following is a list of Events and Logging for Session Authenticator.

## Register

- Default Register
    - Post email/username/password
        - OK → event `register` and `login`
        - NG → no event
- Register with Email Activation
    1. Post email/username/password
        - OK → event `register`
        - NG → no event
    2. Post token
        - OK → event `login`
        - NG → no event

## Login

- Default Login
    - Post email/password
        - OK → event `login` / table `auth_logins`
        - NG → event `failedLogin` / table `auth_logins`
- Email2FA Login
    1. Post email/password
        - OK → no event / table `auth_logins`
        - NG → event `failedLogin` / table `auth_logins`
    2. Post token
        - OK → event `login`
        - NG → no event
- Remember-me
    - Send remember-me cookie w/o session cookie
        - OK → no event
        - NG → no event
- Magic-link
    1. Post email
        - OK → no event
        - NG → no event
    2. Send request with token
        - OK → event `login` and `magicLogin` / table `auth_logins`
        - NG → event `failedLogin` / table `auth_logins`
