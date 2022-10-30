<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Shield\Authentication\Actions\ActionInterface;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\Passwords\ValidatorInterface;
use CodeIgniter\Shield\Models\UserModel;

class Auth extends BaseConfig
{
    /**
     * ////////////////////////////////////////////////////////////////////
     * AUTHENTICATION
     * ////////////////////////////////////////////////////////////////////
     */
    public array $views = [
        'login'                       => '\CodeIgniter\Shield\Views\login',
        'register'                    => '\CodeIgniter\Shield\Views\register',
        'layout'                      => '\CodeIgniter\Shield\Views\layout',
        'action_email_2fa'            => '\CodeIgniter\Shield\Views\email_2fa_show',
        'action_email_2fa_verify'     => '\CodeIgniter\Shield\Views\email_2fa_verify',
        'action_email_2fa_email'      => '\CodeIgniter\Shield\Views\Email\email_2fa_email',
        'action_email_activate_show'  => '\CodeIgniter\Shield\Views\email_activate_show',
        'action_email_activate_email' => '\CodeIgniter\Shield\Views\Email\email_activate_email',
        'magic-link-login'            => '\CodeIgniter\Shield\Views\magic_link_form',
        'magic-link-message'          => '\CodeIgniter\Shield\Views\magic_link_message',
        'magic-link-email'            => '\CodeIgniter\Shield\Views\Email\magic_link_email',
    ];

    /**
     * --------------------------------------------------------------------
     * Redirect urLs
     * --------------------------------------------------------------------
     * The default URL that a user will be redirected to after
     * various auth actions. If you need more flexibility you can
     * override the `getUrl()` method to apply any logic you may need.
     */
    public array $redirects = [
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
     * You must register actions in the order of the actions to be performed.
     *
     * Available actions with Shield:
     * - register: 'CodeIgniter\Shield\Authentication\Actions\EmailActivator'
     * - login:    'CodeIgniter\Shield\Authentication\Actions\Email2FA'
     *
     * @var array<string, class-string<ActionInterface>|null>
     */
    public array $actions = [
        'register' => null,
        'login'    => null,
    ];

    /**
     * --------------------------------------------------------------------
     * Authenticators
     * --------------------------------------------------------------------
     * The available authentication systems, listed
     * with alias and class name. These can be referenced
     * by alias in the auth helper:
     *      auth('tokens')->attempt($credentials);
     *
     * @var array<string, class-string<AuthenticatorInterface>>
     */
    public array $authenticators = [
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
    public array $authenticatorHeader = [
        'tokens' => 'Authorization',
    ];

    /**
     * --------------------------------------------------------------------
     * Unused Token Lifetime
     * --------------------------------------------------------------------
     * Determines the amount of time, in seconds, that an unused
     * access token can be used.
     */
    public int $unusedTokenLifetime = YEAR;

    /**
     * --------------------------------------------------------------------
     * Default Authenticator
     * --------------------------------------------------------------------
     * The Authenticator to use when none is specified.
     * Uses the $key from the $authenticators array above.
     */
    public string $defaultAuthenticator = 'session';

    /**
     * --------------------------------------------------------------------
     * Authentication Chain
     * --------------------------------------------------------------------
     * The Authenticators to test logged in status against
     * when using the 'chain' filter. Each Authenticator listed will be checked.
     * If no match is found, then the next in the chain will be checked.
     *
     * @var string[]
     * @phpstan-var list<string>
     */
    public array $authenticationChain = [
        'session',
        'tokens',
    ];

    /**
     * --------------------------------------------------------------------
     * Allow Registration
     * --------------------------------------------------------------------
     * Determines whether users can register for the site.
     */
    public bool $allowRegistration = true;

    /**
     * --------------------------------------------------------------------
     * Record Last Active Date
     * --------------------------------------------------------------------
     * If true, will always update the `last_active` datetime for the
     * logged in user on every page request.
     */
    public bool $recordActiveDate = true;

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
    public bool $allowMagicLinkLogins = true;

    /**
     * --------------------------------------------------------------------
     * Magic Link Lifetime
     * --------------------------------------------------------------------
     * Specifies the amount of time, in seconds, that a magic link is valid.
     * You can use Time Constants or any desired number.
     */
    public int $magicLinkLifetime = HOUR;

    /**
     * --------------------------------------------------------------------
     * Session Authenticator Configuration
     * --------------------------------------------------------------------
     * These settings only apply if you are using the Session Authenticator
     * for authentication.
     *
     * - field                  The name of the key the current user info is stored in session
     * - allowRemembering       Does the system allow use of "remember-me"
     * - rememberCookieName     The name of the cookie to use for "remember-me"
     * - rememberLength         The length of time, in seconds, to remember a user.
     *
     * @var array<string, bool|int|string>
     */
    public array $sessionConfig = [
        'field'              => 'user',
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
    public int $minimumPasswordLength = 8;

    /**
     * --------------------------------------------------------------------
     * Password Check Helpers
     * --------------------------------------------------------------------
     * The PasswordValidator class runs the password through all of these
     * classes, each getting the opportunity to pass/fail the password.
     * You can add custom classes as long as they adhere to the
     * CodeIgniter\Shield\Authentication\Passwords\ValidatorInterface.
     *
     * @var class-string<ValidatorInterface>[]
     */
    public array $passwordValidators = [
        'CodeIgniter\Shield\Authentication\Passwords\CompositionValidator',
        'CodeIgniter\Shield\Authentication\Passwords\NothingPersonalValidator',
        'CodeIgniter\Shield\Authentication\Passwords\DictionaryValidator',
        // 'CodeIgniter\Shield\Authentication\Passwords\PwnedValidator',
    ];

    /**
     * --------------------------------------------------------------------
     * Valid login fields
     * --------------------------------------------------------------------
     * Fields that are available to be used as credentials for login.
     */
    public array $validFields = [
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
    public array $personalFields = [];

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
    public int $maxSimilarity = 50;

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
    public string $hashAlgorithm = PASSWORD_DEFAULT;

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
    public int $hashMemoryCost = 2048;  // PASSWORD_ARGON2_DEFAULT_MEMORY_COST;

    public int $hashTimeCost = 4;       // PASSWORD_ARGON2_DEFAULT_TIME_COST;
    public int $hashThreads  = 4;        // PASSWORD_ARGON2_DEFAULT_THREADS;

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
    public int $hashCost = 10;

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
     * CodeIgniter\Shield\Models\UserModel.
     *
     * @var class-string<UserModel>
     */
    public string $userProvider = 'CodeIgniter\Shield\Models\UserModel';

    /**
     * Returns the URL that a user should be redirected
     * to after a successful login.
     */
    public function loginRedirect(): string
    {
        $url = setting('Auth.redirects')['login'];

        return $this->getUrl($url);
    }

    /**
     * Returns the URL that a user should be redirected
     * to after they are logged out.
     */
    public function logoutRedirect(): string
    {
        $url = setting('Auth.redirects')['logout'];

        return $this->getUrl($url);
    }

    /**
     * Returns the URL the user should be redirected to
     * after a successful registration.
     */
    public function registerRedirect(): string
    {
        $url = setting('Auth.redirects')['register'];

        return $this->getUrl($url);
    }

    protected function getUrl(string $url): string
    {
        return strpos($url, 'http') === 0
            ? $url
            : rtrim(site_url($url), '/ ');
    }
}
