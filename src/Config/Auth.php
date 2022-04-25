<?php

namespace Sparks\Shield\Config;

use CodeIgniter\Config\BaseConfig;
use Sparks\Shield\Authentication\Handlers\AccessTokens;
use Sparks\Shield\Authentication\Handlers\Session;

class Auth extends BaseConfig
{
    /**
     * ////////////////////////////////////////////////////////////////////
     * AUTHENTICATION
     * ////////////////////////////////////////////////////////////////////
     */
    public $views = [
        'login'                       => '\Sparks\Shield\Views\login',
        'register'                    => '\Sparks\Shield\Views\register',
        'forgotPassword'              => '\Sparks\Shield\Views\forgot_password',
        'resetPassword'               => '\Sparks\Shield\Views\reset_password',
        'layout'                      => '\Sparks\Shield\Views\layout',
        'action_email_2fa'            => '\Sparks\Shield\Views\email_2fa_show',
        'action_email_2fa_verify'     => '\Sparks\Shield\Views\email_2fa_verify',
        'action_email_2fa_email'      => '\Sparks\Shield\Views\email_2fa_email',
        'action_email_activate_email' => '\Sparks\Shield\Views\email_activate_email',
        'action_email_activate_show'  => '\Sparks\Shield\Views\email_activate_show',
        'magic-link-login'            => '\Sparks\Shield\Views\magic_link_form',
        'magic-link-message'          => '\Sparks\Shield\Views\magic_link_message',
        'magic-link-email'            => '\Sparks\Shield\Views\magic_link_email',
    ];

    /**
     * --------------------------------------------------------------------
     * Redirect urLs
     * --------------------------------------------------------------------
     * The default URL that a user will be redirected to after
     * various auth actions. If you need more flexibility you
     * should extend the appropriate controller and overrider the
     * `getRedirect()` methods to apply any logic you may need.
     */
    public $redirects = [
        'register' => '/',
        'login'    => '/',
        'logout'   => 'login',
    ];

    /**
     * --------------------------------------------------------------------
     * Authentication Actions
     * --------------------------------------------------------------------
     * Specifies the class that represents an action to take after
     * the user logs in or registers a new account at the site.
     *
     * Available actions with Shield:
     * - login:    Sparks\Shield\Authentication\Actions\Email2FA
     * - register: Sparks\Shield\Authentication\Actions\EmailActivator
     */
    public $actions = [
        'login'    => null,
        'register' => null,
    ];

    /**
     * --------------------------------------------------------------------
     * Authenticators
     * --------------------------------------------------------------------
     * The available authentication systems, listed
     * with alias and class name. These can be referenced
     * by alias in the auth helper:
     *      auth('api')->attempt($credentials);
     */
    public $authenticators = [
        'tokens'  => AccessTokens::class,
        'session' => Session::class,
    ];

    /**
     * --------------------------------------------------------------------
     * Name of Authenticator Header
     * --------------------------------------------------------------------
     * The name of Header that the Authorization token should be found.
     * According to the specs, this should be `Authorization`, but rare
     * circumstances might need a different header.
     */
    public $authenticatorHeader = [
        'tokens' => 'Authorization',
    ];

    /**
     * --------------------------------------------------------------------
     * Unused Token Lifetime
     * --------------------------------------------------------------------
     * Determines the amount of time, in seconds, that an unused
     * access token can be used.
     */
    public $unusedTokenLifetime = YEAR;

    /**
     * --------------------------------------------------------------------
     * Default Authenticator
     * --------------------------------------------------------------------
     * The authentication handler to use when none is specified.
     * Uses the $key from the $authenticators array above.
     */
    public $defaultAuthenticator = 'session';

    /**
     * --------------------------------------------------------------------
     * Authentication Chain
     * --------------------------------------------------------------------
     * The authentication handlers to test logged in status against
     * when using the 'chain' filter. Each handler listed will be checked.
     * If no match is found, then the next in the chain will be checked.
     */
    public $authenticationChain = [
        'session',
        'tokens',
    ];

    /**
     * --------------------------------------------------------------------
     * Allow Registration
     * --------------------------------------------------------------------
     * Determines whether users can register for the site.
     */
    public $allowRegistration = true;

    /**
     * --------------------------------------------------------------------
     * Record Last Active Date
     * --------------------------------------------------------------------
     * If true, will always update the `last_active` datetime for the
     * logged in user on every page request.
     */
    public $recordActiveDate = true;

    /**
     * --------------------------------------------------------------------
     * Allow Magic Link Logins
     * --------------------------------------------------------------------
     * If true, will allow the use of "magic links" sent via the email
     * as a way to log a user in without the need for a password.
     * By default, this is used in place of a password reset flow, but
     * could be modified as the only method of login once an account
     * has been set up.
     */
    public $allowMagicLinkLogins = true;

    /**
     * --------------------------------------------------------------------
     * Magic Link Lifetime
     * --------------------------------------------------------------------
     * Specifies the amount of time, in seconds, that a magic link is valid.
     */
    public $magicLinkLifetime = 1 * HOUR;

    /**
     * --------------------------------------------------------------------
     * Session Handler Configuration
     * --------------------------------------------------------------------
     * These settings only apply if you are using the Session Handler
     * for authentication.
     *
     * - field                  The name of the key the logged in user is stored in session
     * - allowRemembering       Does the system allow use of "remember-me"
     * - rememberCookieName     The name of the cookie to use for "remember-me"
     * - rememberLength         The length of time, in seconds, to remember a user.
     */
    public $sessionConfig = [
        'field'              => 'logged_in',
        'allowRemembering'   => true,
        'rememberCookieName' => 'remember',
        'rememberLength'     => 30 * DAY,
    ];

    /**
     * --------------------------------------------------------------------
     * Minimum Password Length
     * --------------------------------------------------------------------
     * The minimum length that a password must be to be accepted.
     * Recommended minimum value by NIST = 8 characters.
     */
    public $minimumPasswordLength = 8;

    /**
     * --------------------------------------------------------------------
     * Password Check Helpers
     * --------------------------------------------------------------------
     * The PasswordValidator class runs the password through all of these
     * classes, each getting the opportunity to pass/fail the password.
     * You can add custom classes as long as they adhere to the
     * Sparks\Shield\Authentication\Passwords\ValidatorInterface.
     */
    public $passwordValidators = [
        'Sparks\Shield\Authentication\Passwords\CompositionValidator',
        'Sparks\Shield\Authentication\Passwords\NothingPersonalValidator',
        'Sparks\Shield\Authentication\Passwords\DictionaryValidator',
        //'Sparks\Shield\Authentication\Passwords\PwnedValidator',
    ];

    /**
     * --------------------------------------------------------------------
     * Valid login fields
     * --------------------------------------------------------------------
     * Fields that are available to be used as credentials for login.
     */
    public $validFields = [
        'email',
        'username',
    ];

    /**
     * --------------------------------------------------------------------
     * Additional Fields for "Nothing Personal"
     * --------------------------------------------------------------------
     * The NothingPersonalValidator prevents personal information from
     * being used in passwords. The email and username fields are always
     * considered by the validator. Do not enter those field names here.
     *
     * An extended User Entity might include other personal info such as
     * first and/or last names. $personalFields is where you can add
     * fields to be considered as "personal" by the NothingPersonalValidator.
     * For example:
     *     $personalFields = ['firstname', 'lastname'];
     */
    public $personalFields = [];

    /**
     * --------------------------------------------------------------------
     * Password / Username Similarity
     * --------------------------------------------------------------------
     * Among other things, the NothingPersonalValidator checks the
     * amount of sameness between the password and username.
     * Passwords that are too much like the username are invalid.
     *
     * The value set for $maxSimilarity represents the maximum percentage
     * of similarity at which the password will be accepted. In other words, any
     * calculated similarity equal to, or greater than $maxSimilarity
     * is rejected.
     *
     * The accepted range is 0-100, with 0 (zero) meaning don't check similarity.
     * Using values at either extreme of the *working range* (1-100) is
     * not advised. The low end is too restrictive and the high end is too permissive.
     * The suggested value for $maxSimilarity is 50.
     *
     * You may be thinking that a value of 100 should have the effect of accepting
     * everything like a value of 0 does. That's logical and probably true,
     * but is unproven and untested. Besides, 0 skips the work involved
     * making the calculation unlike when using 100.
     *
     * The (admittedly limited) testing that's been done suggests a useful working range
     * of 50 to 60. You can set it lower than 50, but site users will probably start
     * to complain about the large number of proposed passwords getting rejected.
     * At around 60 or more it starts to see pairs like 'captain joe' and 'joe*captain' as
     * perfectly acceptable which clearly they are not.
     *
     * To disable similarity checking set the value to 0.
     *     public $maxSimilarity = 0;
     */
    public $maxSimilarity = 50;

    /**
     * --------------------------------------------------------------------
     * Encryption Algorithm to use
     * --------------------------------------------------------------------
     * Valid values are
     * - PASSWORD_DEFAULT (default)
     * - PASSWORD_BCRYPT
     * - PASSWORD_ARGON2I  - As of PHP 7.2 only if compiled with support for it
     * - PASSWORD_ARGON2ID - As of PHP 7.3 only if compiled with support for it
     *
     * If you choose to use any ARGON algorithm, then you might want to
     * uncomment the "ARGON2i/D Algorithm" options to suit your needs
     */
    public $hashAlgorithm = PASSWORD_DEFAULT;

    /**
     * --------------------------------------------------------------------
     * ARGON2i/D Algorithm options
     * --------------------------------------------------------------------
     * The ARGON2I method of encryption allows you to define the "memory_cost",
     * the "time_cost" and the number of "threads", whenever a password hash is
     * created.
     * This defaults to a value of 10 which is an acceptable number.
     * However, depending on the security needs of your application
     * and the power of your hardware, you might want to increase the
     * cost. This makes the hashing process takes longer.
     */
    public $hashMemoryCost = 2048;  // PASSWORD_ARGON2_DEFAULT_MEMORY_COST;

    public $hashTimeCost = 4;       // PASSWORD_ARGON2_DEFAULT_TIME_COST;
    public $hashThreads  = 4;        // PASSWORD_ARGON2_DEFAULT_THREADS;

    /**
     * --------------------------------------------------------------------
     * Password Hashing Cost
     * --------------------------------------------------------------------
     * The BCRYPT method of encryption allows you to define the "cost"
     * or number of iterations made, whenever a password hash is created.
     * This defaults to a value of 10 which is an acceptable number.
     * However, depending on the security needs of your application
     * and the power of your hardware, you might want to increase the
     * cost. This makes the hashing process takes longer.
     *
     * Valid range is between 4 - 31.
     */
    public $hashCost = 10;

    /**
     * ////////////////////////////////////////////////////////////////////
     * OTHER SETTINGS
     * ////////////////////////////////////////////////////////////////////
     */

    /**
     * --------------------------------------------------------------------
     * User Provider
     * --------------------------------------------------------------------
     * The name of the class that handles user persistence.
     * By default, this is the included UserModel, which
     * works with any of the database engines supported by CodeIgniter.
     * You can change it as long as they adhere to the
     * Sparks\Shield\Interfaces\UserProvider.
     *
     * @var class-string<\Sparks\Shield\Interfaces\UserProvider>
     */
    public $userProvider = 'Sparks\Shield\Models\UserModel';

    /**
     * Returns the URL that a user should be redirected
     * to after a successful login.
     */
    public function loginRedirect(): string
    {
        $url = setting('Auth.redirects')['login'];

        return strpos($url, 'http') === 0
            ? $url
            : rtrim(site_url($url), '/ ');
    }

    /**
     * Returns the URL that a user should be redirected
     * to after they are logged out.
     */
    public function logoutRedirect(): string
    {
        $url = setting('Auth.redirects')['logout'];

        return strpos($url, 'http') === 0
            ? $url
            : rtrim(site_url($url), '/ ');
    }

    /**
     * Returns the URL the user should be redirected to
     * after a successful registration.
     */
    public function getRedirectURL(): string
    {
        $url = setting('Auth.redirects')['register'];

        return strpos($url, 'http') === 0
            ? $url
            : rtrim(site_url($url), '/ ');
    }
}
