<?php

namespace Sparks\Shield\Authentication\Handlers;

use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use Exception;
use InvalidArgumentException;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Authentication\AuthenticatorInterface;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Interfaces\Authenticatable;
use Sparks\Shield\Models\LoginModel;
use Sparks\Shield\Models\RememberModel;
use Sparks\Shield\Result;

class Session implements AuthenticatorInterface
{
    /**
     * The persistence engine
     */
    protected $provider;

    protected ?Authenticatable $user = null;
    protected LoginModel $loginModel;

    /**
     * Should the user be remembered?
     */
    protected bool $shouldRemember = false;

    protected RememberModel $rememberModel;

    public function __construct($provider)
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
    public function remember(bool $shouldRemember = true)
    {
        $this->shouldRemember = $shouldRemember;

        return $this;
    }

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @return Result
     */
    public function attempt(array $credentials)
    {
        $request   = service('request');
        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent();
        $result    = $this->check($credentials);

        if (! $result->isOK()) {
            // Always record a login attempt, whether success or not.
            $this->loginModel->recordLoginAttempt($credentials['email'] ?? $credentials['username'], false, $ipAddress, $userAgent);

            $this->user = null;

            // Fire an event on failure so devs have the chance to
            // let them know someone attempted to login to their account
            Events::trigger('failedLoginAttempt', $credentials);

            return $result;
        }

        $this->login($result->extraInfo());

        $this->loginModel->recordLoginAttempt($credentials['email'] ?? $credentials['username'], true, $ipAddress, $userAgent, $this->user->getAuthId());

        return $result;
    }

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     *
     * @return Result
     */
    public function check(array $credentials)
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
        $givenPassword = $credentials['password'] ?? null;
        unset($credentials['password']);

        // Find the existing user
        $user = $this->provider->findByCredentials($credentials);

        if ($user === null) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badAttempt'),
            ]);
        }

        // Now, try matching the passwords.
        $passwords = service('passwords');

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
        if ($this->user instanceof Authenticatable) {
            return true;
        }

        if ($userId = session(setting('Auth.sessionConfig')['fieldUser'])) {
            $this->user = $this->provider->findById($userId);

            return $this->user instanceof Authenticatable;
        }

        return false;
    }

    /**
     * Checks if the user needs to proceed with 2FA.
     */
    public function logged2Fa(): bool
    {
        if (setting('Auth.actions')['login'] instanceof ActionInterface) {
            return session(setting('Auth.sessionConfig')['field2Fa']) === true;
        }

        return true;
    }
    
    /**
     * Logs the given user in.
     *
     * @return bool
     */
    public function login(Authenticatable $user)
    {
        /**
         * @todo Authenticatable should define getEmailIdentity() or this should require User
         */
        if (! $user instanceof User) {
            throw new InvalidArgumentException(self::class . '::login() only accepts Shield User');
        }

        $this->user = $user;

        // Update the user's last used date on their password identity.
        $this->user->touchIdentity($this->user->getEmailIdentity());

        // Regenerate the session ID to help protect against session fixation
        if (ENVIRONMENT !== 'testing') {
            session()->regenerate();
        }

        // Let the session know we're logged in
        session()->set(setting('Auth.sessionConfig')['fieldUser'], $this->user->getAuthId());

        // When logged in, ensure cache control headers are in place
        service('response')->noCache();

        if ($this->shouldRemember && setting('Auth.sessionConfig')['allowRemembering']) {
            $this->rememberUser($this->user->getAuthId());

            // Reset so it doesn't mess up future calls.
            $this->shouldRemember = false;
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
     * @return mixed
     */
    public function loginById(int $userId)
    {
        $user = $this->provider->findById($userId);

        if (empty($user)) {
            throw AuthenticationException::forInvalidUser();
        }

        return $this->login($user);
    }

    /**
     * Logs the current user out.
     *
     * @return bool
     */
    public function logout()
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

        // Take care of any remember me functionality
        $this->rememberModel->purgeRememberTokens($this->user->getAuthId());

        // Trigger logout event
        $result = Events::trigger('logout', $this->user);

        $this->user = null;

        return $result;
    }

    /**
     * Removes any remember-me tokens, if applicable.
     *
     * @param int|null $id ID of user to forget.
     *
     * @return void
     */
    public function forget(?int $id = null)
    {
        if (empty($id)) {
            if (! $this->loggedIn()) {
                return;
            }

            $id = $this->user->getAuthId();
        }

        $this->rememberModel->purgeRememberTokens($id);
    }

    /**
     * Returns the current user instance.
     *
     * @return Authenticatable|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Updates the user's last active date.
     *
     * @return mixed
     */
    public function recordActive()
    {
        if (! $this->user instanceof User) {
            throw new InvalidArgumentException(self::class . '::recordActive() requires logged in user before calling.');
        }

        $this->user->last_active = Time::now();

        $this->provider->save($this->user);
    }

    /**
     * Generates a timing-attack safe remember me token
     * and stores the necessary info in the db and a cookie.
     *
     * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
     *
     * @throws Exception
     */
    protected function rememberUser(int $userID)
    {
        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(20));
        $expires   = date('Y-m-d H:i:s', time() + setting('Auth.sessionConfig')['rememberLength']);

        $token = $selector . ':' . $validator;

        // Store it in the database
        $this->rememberModel->rememberUser($userID, $selector, hash('sha256', $validator), $expires);

        // Save it to the user's browser in a cookie.
        $response = service('response');

        // Create the cookie
        $response->setCookie(
            setting('Auth.sessionConfig')['rememberCookieName'],
            $token,                                             // Value
            setting('Auth.sessionConfig')['rememberLength'],    // # Seconds until it expires
            setting('App.cookieDomain'),
            setting('App.cookiePath'),
            setting('App.cookiePrefix'),
            false,                          // Only send over HTTPS?
            true                            // Hide from Javascript?
        );
    }
}
