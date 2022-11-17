<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Language\it;

return [
    // Exceptions
    'unknownAuthenticator'  => '{0} non è un autenticatore valido.',
    'unknownUserProvider'   => 'Impossibile determinare lo User Provider da usare.',
    'invalidUser'           => 'Impossibile trovere l\'utente specificato.',
    'badAttempt'            => 'Impossibile accedere. Si prega di verificare le proprie credenziali.',
    'noPassword'            => 'Impossibile validare un utente senza una password.',
    'invalidPassword'       => 'Impossibile accedere. Si prega di verificare la propria password.',
    'noToken'               => 'Ogni richiesta deve avere un token bearer nell\' header {0}.',
    'badToken'              => 'Il token di accesso non è valido.',
    'oldToken'              => 'Il token di accesso è scaduto.',
    'noUserEntity'          => 'Deve essere fornita una User Entity per la validazione della password.',
    'invalidEmail'          => 'Impossibile verificare che l\'indirizzo email corrisponda all\'email nel record.',
    'unableSendEmailToUser' => 'Spiacente, c\'è stato un problema inviando l\'email. Non possiamo inviare un\'email a "{0}".',
    'throttled'             => 'Too many requests made from this IP address. You may try again in {0} seconds.',
    'throttled'             => 'Troppe richieste effettuate da questo indirizzo IP. Potrai riprovare tra {0} secondi.',

    'email'           => 'Indirizzo Email',
    'username'        => 'Username',
    'password'        => 'Password',
    'passwordConfirm' => 'Password (ancora)',
    'haveAccount'     => 'Hai già un account?',

    // Buttons
    'confirm' => 'Conferma',
    'send'    => 'Invia',

    // Registration
    'register'         => 'Register',
    'registerDisabled' => 'Registration is not currently allowed.',
    'registerSuccess'  => 'Welcome aboard!',

    // Login
    'login'              => 'Login',
    'needAccount'        => 'Need an account?',
    'rememberMe'         => 'Remember me?',
    'forgotPassword'     => 'Forgot your password?',
    'useMagicLink'       => 'Use a Login Link',
    'magicLinkSubject'   => 'Your Login Link',
    'magicTokenNotFound' => 'Unable to verify the link.',
    'magicLinkExpired'   => 'Sorry, link has expired.',
    'checkYourEmail'     => 'Check your email!',
    'magicLinkDetails'   => 'We just sent you an email with a Login link inside. It is only valid for {0} minutes.',
    'successLogout'      => 'You have successfully logged out.',

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

    // Email Globals
    'emailInfo'      => 'Some information about the person:',
    'emailIpAddress' => 'IP Address:',
    'emailDevice'    => 'Device:',
    'emailDate'      => 'Date:',

    // 2FA
    'email2FATitle'       => 'Two Factor Authentication',
    'confirmEmailAddress' => 'Confirm your email address.',
    'emailEnterCode'      => 'Confirm your Email',
    'emailConfirmCode'    => 'Enter the 6-digit code we just sent to your email address.',
    'email2FASubject'     => 'Your authentication code',
    'email2FAMailBody'    => 'Your authentication code is:',
    'invalid2FAToken'     => 'The code was incorrect.',
    'need2FA'             => 'You must complete a two-factor verification.',
    'needVerification'    => 'Check your email to complete account activation.',

    // Activate
    'emailActivateTitle'    => 'Email Activation',
    'emailActivateBody'     => 'We just sent an email to you with a code to confirm your email address. Copy that code and paste it below.',
    'emailActivateSubject'  => 'Your activation code',
    'emailActivateMailBody' => 'Please use the code below to activate your account and start using the site.',
    'invalidActivateToken'  => 'The code was incorrect.',
    'needActivate'          => 'You must complete your registration by confirming the code sent to your email address.',

    // Groups
    'unknownGroup' => '{0} is not a valid group.',
    'missingTitle' => 'Groups must have a title.',

    // Permissions
    'unknownPermission' => '{0} is not a valid permission.',
];
