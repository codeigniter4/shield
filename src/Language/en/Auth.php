<?php

return [
	// Exceptions
   'unknownHandler'            => '{0} is not a valid auth handler.',
   'unknownUserProvider'       => 'Unable to determine the User Provider to use.',
   'invalidUser'               => 'Unable to locate the specified user.',
   'badAttempt'                => 'Unable to log you in. Please check your credentials.',
   'noPassword'                => 'Cannot validate a user without a password.',
   'invalidPassword'           => 'Unable to log you in. Please check your password.',
   'noToken'                   => 'Every request must have a bearer token in the Authorization header.',
   'badToken'                  => 'The access token is invalid.',
   'noUserEntity'              => 'User Entity must be provided for password validation.',

   'register'                  => 'Register',
   'email'                     => 'Email Address',
   'username'                  => 'Username',
   'password'                  => 'Password',
   'passwordConfirm'           => 'Password (again)',
   'haveAccount'               => 'Already have an account?',

	// Login
   'login'                     => 'Login',

	// Passwords
   'errorPasswordLength'       => 'Passwords must be at least {0, number} characters long.',
   'suggestPasswordLength'     => 'Pass phrases - up to 255 characters long - make more secure passwords that are easy to remember.',
   'errorPasswordCommon'       => 'Password must not be a common password.',
   'suggestPasswordCommon'     => 'The password was checked against over 65k commonly used passwords or passwords that have been leaked through hacks.',
   'errorPasswordPersonal'     => 'Passwords cannot contain re-hashed personal information.',
   'suggestPasswordPersonal'   => 'Variations on your email address or username should not be used for passwords.',
   'errorPasswordTooSimilar'   => 'Password is too similar to the username.',
   'suggestPasswordTooSimilar' => 'Do not use parts of your username in your password.',
   'errorPasswordPwned'        => 'The password {0} has been exposed due to a data breach and has been seen {1, number} times in {2} of compromised passwords.',
   'suggestPasswordPwned'      => '{0} should never be used as a password. If you are using it anywhere change it immediately.',
   'errorPasswordEmpty'        => 'A Password is required.',
   'passwordChangeSuccess'     => 'Password changed successfully',
   'userDoesNotExist'          => 'Password was not changed. User does not exist',
   'resetTokenExpired'         => 'Sorry. Your reset token has expired.',
];
