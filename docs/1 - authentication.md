# Authentication

Shield provides a flexible, secure, authentication system for your web apps and API's. 

## Available Handlers

Shield ships with 3 handlers to handle several typicaly situations within web app development. 

### Session Handler

The Session handler stores the user's authentication within the user's session, and on a secure cookie
on their device. This is the standard password-based login used in most web sites. It supports a 
secure remember me feature, and more. This can also be used to handle authentication for 
single page applications (SPAs).

### Token Handler 

The Token handler supports the use of revokable API tokens without using OAuth. These are commonly 
used to provide third-party developers access to your API. These tokens typically have a very long 
expiration time, often years. 

These are also suitable for use with mobile applications. In this case, the user would register/sign-in
with their email/password. The application would create a new access token for them, with a recognizable
name, like Lonnie's iPhone 12, and return it to the mobile application, where it is stored and used
in all future requests. 
