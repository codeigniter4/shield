<?php

namespace CodeIgniter\Shield\Authentication\Authenticators;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\RememberModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Result;
use Exception;
use InvalidArgumentException;
use stdClass;

class Session implements AuthenticatorInterface
{
    /**
     * The persistence engine
     */
    protected UserModel $provider;

    protected ?User $user = null;
    protected LoginModel $loginModel;

    /**
     * Should the user be remembered?
     */
    protected bool $shouldRemember = false;

    protected RememberModel $rememberModel;

    public function __construct(UserModel $provider)
    {
        helper('setting');
        $this->provider      = $provider;
        $this->loginModel    = model(LoginModel::class); // @phpstan-ignore-line
        $this->rememberModel = model(RememberModel::class); // @phpstan-ignore-line
    }

    /**
     * Sets the $shouldRemember flag
     *
     * @return $this
     */
    public function remember(bool $shouldRemember = true): self
    {
        $this->shouldRemember = $shouldRemember;

        return $this;
    }

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @phpstan-param array{email?: string, username?: string, password?: string} $credentials
     */
    public function attempt(array $credentials): Result
    {
        /** @var IncomingRequest $request */
        $request = service('request');

        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent();

        $result = $this->check($credentials);
        if (! $result->isOK()) {
            // Always record a login attempt, whether success or not.
            $this->recordLoginAttempt($credentials, false, $ipAddress, $userAgent);

            $this->user = null;

            // Fire an event on failure so devs have the chance to
            // let them know someone attempted to login to their account
            unset($credentials['password']);
            Events::trigger('failedLogin', $credentials);

            return $result;
        }

        /** @var User $user */
        $user = $result->extraInfo();

        $this->login($user);

        $this->recordLoginAttempt($credentials, true, $ipAddress, $userAgent, $user->getAuthId());

        // If an action has been defined for login, start it up.
        $actionClass = setting('Auth.actions')['login'] ?? null;
        if (! empty($actionClass)) {
            session()->set('auth_action', $actionClass);
        }

        return $result;
    }

    /**
     * @param int|string|null $userId
     */
    private function recordLoginAttempt(
        array $credentials,
        bool $success,
        string $ipAddress,
        string $userAgent,
        $userId = null
    ): void {
        $this->loginModel->recordLoginAttempt(
            $credentials['email'] ?? $credentials['username'],
            $success,
            $ipAddress,
            $userAgent,
            $userId
        );
    }

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     *
     * @phpstan-param array{email?: string, username?: string, password?: string} $credentials
     */
    public function check(array $credentials): Result
    {
        // Can't validate without a password.
        if (empty($credentials['password']) || count($credentials) < 2) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badAttempt'),
            ]);
        }

        // Remove the password from credentials so we can
        // check afterword.
        $givenPassword = $credentials['password'];
        unset($credentials['password']);

        // Find the existing user
        $user = $this->provider->findByCredentials($credentials);

        if ($user === null) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badAttempt'),
            ]);
        }

        /** @var Passwords $passwords */
        $passwords = service('passwords');

        // Now, try matching the passwords.
        if (! $passwords->verify($givenPassword, $user->password_hash)) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.invalidPassword'),
            ]);
        }

        // Check to see if the password needs to be rehashed.
        // This would be due to the hash algorithm or hash
        // cost changing since the last time that a user
        // logged in.
        if ($passwords->needsRehash($user->password_hash)) {
            $user->password_hash = $passwords->hash($givenPassword);
            $this->provider->save($user);
        }

        return new Result([
            'success'   => true,
            'extraInfo' => $user,
        ]);
    }

    /**
     * Checks if the user is currently logged in.
     */
    public function loggedIn(): bool
    {
        if ($this->user instanceof User) {
            return true;
        }

        /** @var int|string|null $userId */
        $userId = session(setting('Auth.sessionConfig')['field']);

        if ($userId !== null) {
            $this->user = $this->provider->findById($userId);

            return $this->user instanceof User;
        }

        // Check remember-me token.
        if (setting('Auth.sessionConfig')['allowRemembering']) {
            return $this->checkRememberMe();
        }

        return false;
    }

    private function checkRememberMe(): bool
    {
        // Get remember-me token.
        $remember = $this->getRememberMeToken();
        if ($remember === null) {
            return false;
        }

        // Check the remember-me token.
        $token = $this->checkRememberMeToken($remember);
        if ($token === false) {
            return false;
        }

        $user = $this->provider->findById($token->user_id);

        $this->login($user);

        $this->refreshRememberMeToken($token);

        return true;
    }

    private function getRememberMeToken(): ?string
    {
        helper('cookie');

        return get_cookie('remember');
    }

    /**
     * @return false|stdClass
     */
    private function checkRememberMeToken(string $remember)
    {
        [$selector, $validator] = explode(':', $remember);

        $hashedValidator = hash('sha256', $validator);

        $token = $this->rememberModel->getRememberToken($selector);

        if ($token === null) {
            return false;
        }

        if (hash_equals($token->hashedValidator, $hashedValidator) === false) {
            return false;
        }

        return $token;
    }

    /**
     * Logs the given user in.
     */
    public function login(User $user): bool
    {
        $this->user = $user;

        // Update the user's last used date on their password identity.
        $this->user->touchIdentity($this->user->getEmailIdentity());

        // Regenerate the session ID to help protect against session fixation
        if (ENVIRONMENT !== 'testing') {
            session()->regenerate();
        }

        // Let the session know we're logged in
        session()->set(setting('Auth.sessionConfig')['field'], $this->user->getAuthId());

        /** @var Response $response */
        $response = service('response');

        // When logged in, ensure cache control headers are in place
        $response->noCache();

        if ($this->shouldRemember && setting('Auth.sessionConfig')['allowRemembering']) {
            $this->rememberUser($this->user->getAuthId());

            // Reset so it doesn't mess up future calls.
            $this->shouldRemember = false;
        } elseif ($this->getRememberMeToken()) {
            // Remove incoming remember-me token
            delete_cookie(setting('Auth.sessionConfig')['rememberCookieName']);

            // @TODO delete the token record.
        }

        // We'll give a 20% chance to need to do a purge since we
        // don't need to purge THAT often, it's just a maintenance issue.
        // to keep the table from getting out of control.
        if (random_int(1, 100) <= 20) {
            $this->rememberModel->purgeOldRememberTokens();
        }

        // Trigger login event, in case anyone cares
        return Events::trigger('login', $user);
    }

    /**
     * Logs a user in based on their ID.
     *
     * @param int|string $userId
     */
    public function loginById($userId): void
    {
        $user = $this->provider->findById($userId);

        if (empty($user)) {
            throw AuthenticationException::forInvalidUser();
        }

        $this->login($user);
    }

    /**
     * Logs the current user out.
     */
    public function logout(): bool
    {
        if ($this->user === null) {
            return true;
        }

        helper('cookie');

        // Destroy the session data - but ensure a session is still
        // available for flash messages, etc.
        if (isset($_SESSION)) {
            foreach (array_keys($_SESSION) as $key) {
                $_SESSION[$key] = null;
                unset($_SESSION[$key]);
            }
        }

        // Regenerate the session ID for a touch of added safety.
        session()->regenerate(true);

        // Take care of any remember-me functionality
        $this->rememberModel->purgeRememberTokens($this->user->getAuthId());

        // Trigger logout event
        $result = Events::trigger('logout', $this->user);

        $this->user = null;

        return $result;
    }

    /**
     * Removes any remember-me tokens, if applicable.
     *
     * @param int|string|null $userId ID of user to forget.
     */
    public function forget($userId = null): void
    {
        if (empty($userId)) {
            if (! $this->loggedIn()) {
                return;
            }

            $userId = $this->user->getAuthId();
        }

        $this->rememberModel->purgeRememberTokens($userId);
    }

    /**
     * Returns the current user instance.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Updates the user's last active date.
     */
    public function recordActiveDate(): void
    {
        if (! $this->user instanceof User) {
            throw new InvalidArgumentException(
                self::class . '::recordActiveDate() requires logged in user before calling.'
            );
        }

        $this->user->last_active = Time::now();

        $this->provider->save($this->user);
    }

    /**
     * Generates a timing-attack safe remember-me token
     * and stores the necessary info in the db and a cookie.
     *
     * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
     *
     * @param int|string $userId
     *
     * @throws Exception
     */
    protected function rememberUser($userId): void
    {
        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(20));
        $expires   = $this->calcExpires();

        $rawToken = $selector . ':' . $validator;

        // Store it in the database.
        $this->rememberModel->rememberUser(
            $userId,
            $selector,
            $this->hashValidator($validator),
            $expires
        );

        $this->setRememberMeCookie($rawToken);
    }

    private function calcExpires(): string
    {
        return date(
            'Y-m-d H:i:s',
            time() + setting('Auth.sessionConfig')['rememberLength']
        );
    }

    private function setRememberMeCookie(string $rawToken): void
    {
        /** @var Response $response */
        $response = service('response');

        // Save it to the user's browser in a cookie.
        // Create the cookie
        $response->setCookie(
            setting('Auth.sessionConfig')['rememberCookieName'],
            $rawToken,                                             // Value
            setting('Auth.sessionConfig')['rememberLength'],    // # Seconds until it expires
            setting('App.cookieDomain'),
            setting('App.cookiePath'),
            setting('App.cookiePrefix'),
            false,                          // Only send over HTTPS?
            true                            // Hide from Javascript?
        );
    }

    /**
     * Hash remember-me validator
     */
    private function hashValidator(string $validator): string
    {
        return hash('sha256', $validator);
    }

    private function refreshRememberMeToken(stdClass $token)
    {
        // Update validator.
        $validator = bin2hex(random_bytes(20));

        $token->validator = $this->hashValidator($validator);
        $token->expires   = $this->calcExpires();

        $this->rememberModel->updateRememberValidator($token);

        $rawToken = $token->selector . ':' . $validator;

        $this->setRememberMeCookie($rawToken);
    }
}
