# CodeIgniter Shield

Shield is an authentication and authorization framework for CodeIgniter 4. While it does provide a base set of tools
that are commonly used in websites, it is designed to be flexible and easily customizable.  

The primary goals for Shield are: 
1. It must be very flexible and allow developers to extend/override almost any part of it.
2. It must have security at its core. It is an auth lib after all.
3. To cover many auth needs right out of the box, but be simple to add additional functionality to.

## Authentication

Shield provides two primary methods of authentication out of the box: 

**Session-based** 

This is your typical email/username/password system you see everywhere. It includes a secure "remember me" functionality.
This can be used for standard web applications, as well as for single page applications. Includes full controllers and 
basic views for all standard functionality, like registration, login, forgot password, etc.

**Personal Access Codes** 

These are much like the access codes that GitHub uses, where they are unique to a single user, and a single user
can have more than one. This can be used for API authentication of third-party users, and even for allowing 
access for a mobile application that you build. 

## Further Reading (or "Our Inspirations")

The following articles/sites have been fundamental in shaping the security and best practices used
within this library, in no particular order: 

- [Google Cloud: 12 Best Practices For User Accounts](https://cloud.google.com/blog/products/gcp/12-best-practices-for-user-account)
- [NIST Digital Identity Guidelines](https://pages.nist.gov/800-63-3/sp800-63b.html)
- [Implementing Secure User Authentication in PHP Applications with Long-Term Persistence (Login with "Remember Me" Cookies) ](https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence)
-  
