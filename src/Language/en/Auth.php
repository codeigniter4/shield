<?php

namespace Sparks\Shield\Language\en;

return [
    // Exceptions
    'unknownHandler'      => '{0} is not a valid auth handler.',
    'unknownUserProvider' => 'Unable to determine the User Provider to use.',
    'invalidUser'         => 'Unable to locate the specified user.',
    'badAttempt'          => 'Unable to log you in. Please check your credentials.',
    'noPassword'          => 'Cannot validate a user without a password.',
    'invalidPassword'     => 'Unable to log you in. Please check your password.',
    'noToken'             => 'Every request must have a bearer token in the Authorization header.',
    'badToken'            => 'The access token is invalid.',
    'oldToken'            => 'The access token has expired.',
    'noUserEntity'        => 'User Entity must be provided for password validation.',
    'invalidEmail'        => 'Unable to verify the email address matches the email on record.',

    'email'           => 'Email Address',
    'username'        => 'Username',
    'password'        => 'Password',
    'passwordConfirm' => 'Password (again)',
    'haveAccount'     => 'Already have an account?',
    'confirm'         => 'Confirm',

    // Registration
    'register'         => 'Register',
    'registerDisabled' => 'Registration is not currently allowed.',

    // Login
    'login'              => 'Login',
    'needAccount'        => 'Need an account?',
    'rememberMe'         => 'Remember me?',
    'forgotPassword'     => 'Forgot your password?',
    'useMagicLink'       => 'Use a Login Link',
    'magicLinkSubject'   => 'Your Login Link',
    'magicTokenNotFound' => 'Unable to verify the link.',
    'magicLinkExpired'   => 'Sorry, link has expired.',
    'checkYourEmail'     => 'Check your email',
    'magicLinkDetails'   => 'We just sent you an email with a Login link inside. It is only valid for {0} minutes.',

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

    // 2FA
    'email2FATitle'    => 'Two Factor Authentication',
    'emailEnterCode'   => 'Confirm your Email',
    'emailConfirmCode' => 'Enter the 6-digit code we just sent to your email address.',
    'email2FASubject'  => 'Confirm your email address',
    'invalid2FAToken'  => 'The token was incorrect.',
    'need2FA'          => 'You must complete a two-factor verification.',
    'needVerification' => 'Check your email to complete account activation.',

    // Activate
    'emailActivateTitle'   => 'Email Activation',
    'emailActivateSubject' => 'Confirm your Email',

    // Groups
    'unknownGroup' => '{0} is not a valid group.',
    'missingTitle' => 'Groups must have a title.',

    // Permissions
    'unknownPermission' => '{0} is not a valid permission.',
];
