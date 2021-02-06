<?php

namespace Sparks\Shield\Authentication\Handlers;

use CodeIgniter\Events\Events;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Authentication\AuthenticatorInterface;
use Sparks\Shield\Config\Auth;
use Sparks\Shield\Interfaces\Authenticatable;
use Config\App;
use \Sparks\Shield\Models\LoginModel;
use \Sparks\Shield\Models\RememberModel;
use Sparks\Shield\Result;

class Session implements AuthenticatorInterface
{
	/**
	 * @var array
	 */
	protected $config;

	/**
	 * The persistence engine
	 */
	protected $provider;

	/**
	 * @var \Sparks\Shield\Interfaces\Authenticatable
	 */
	protected $user;

	/**
	 * @var LoginModel
	 */
	protected $loginModel;

	/**
	 * Should the user be remembered?
	 *
	 * @var boolean
	 */
	protected $shouldRemember = false;

	/**
	 * @var RememberModel
	 */
	protected $rememberModel;

	public function __construct(Auth $config, $provider)
	{
		$this->config        = $config->sessionConfig;
		$this->provider      = $provider;
		$this->loginModel    = model(LoginModel::class);
		$this->rememberModel = model(RememberModel::class);
	}

	/**
	 * Sets the $shouldRemember flag
	 *
	 * @param boolean $shouldRemember
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
	 * @param array $credentials
	 *
	 * @return Result
	 */
	public function attempt(array $credentials)
	{
		$ipAddress = service('request')->getIPAddress();
		$result    = $this->check($credentials);

		if (! $result->isOK())
		{
			// Always record a login attempt, whether success or not.
			$this->loginModel->recordLoginAttempt($credentials['email'] ?? $credentials['username'], false, $ipAddress, null);

			$this->user = null;

			// Fire an event on failure so devs have the chance to
			// let them know someone attempted to login to their account
			Events::trigger('failedLoginAttempt', $credentials);

			return $result;
		}

		$this->login($result->extraInfo());

		$this->loginModel->recordLoginAttempt($credentials['email'] ?? $credentials['username'], true, $ipAddress, $this->user->id ?? null);

		return $result;
	}

	/**
	 * Checks a user's $credentials to see if they match an
	 * existing user.
	 *
	 * @param array $credentials
	 *
	 * @return Result
	 */
	public function check(array $credentials)
	{
		// Can't validate without a password.
		if (empty($credentials['password']) || count($credentials) < 2)
		{
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

		if ($user === null)
		{
			return new Result([
				'success' => false,
				'reason'  => lang('Auth.badAttempt'),
			]);
		}

		// Now, try matching the passwords.
		$passwords = service('passwords');

		if (! $passwords->verify($givenPassword, $user->password_hash))
		{
			return new Result([
				'success' => false,
				'reason'  => lang('Auth.invalidPassword'),
			]);
		}

		// Check to see if the password needs to be rehashed.
		// This would be due to the hash algorithm or hash
		// cost changing since the last time that a user
		// logged in.
		if ($passwords->needsRehash($user->password_hash))
		{
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
	 *
	 * @return boolean
	 */
	public function loggedIn(): bool
	{
		if ($this->user instanceof Authenticatable)
		{
			return true;
		}

		if ($userId = session($this->config['field']))
		{
			$this->user = $this->provider->findById($userId);

			return $this->user instanceof Authenticatable;
		}

		return false;
	}

	/**
	 * Logs the given user in.
	 *
	 * @param Authenticatable $user
	 *
	 * @return mixed
	 */
	public function login(Authenticatable $user)
	{
		$this->user = $user;

		// Always record a login attempt
		$ipAddress = service('request')->getIPAddress();
		$this->recordLoginAttempt($user->getAuthEmail(), true, $ipAddress, $user->getAuthId() ?? null);

		// Update the user's last used date on their password identity.
		$this->user->touchIdentity($this->user->getEmailIdentity());

		// Regenerate the session ID to help protect against session fixation
		if (ENVIRONMENT !== 'testing')
		{
			session()->regenerate();
		}

		// Let the session know we're logged in
		session()->set($this->config['field'], $this->user->id);

		// When logged in, ensure cache control headers are in place
		service('response')->noCache();

		if ($this->shouldRemember && $this->config['allowRemembering'])
		{
			$this->rememberUser($this->user->id);

			// Reset so it doesn't mess up future calls.
			$this->shouldRemember = false;
		}

		// We'll give a 20% chance to need to do a purge since we
		// don't need to purge THAT often, it's just a maintenance issue.
		// to keep the table from getting out of control.
		if (mt_rand(1, 100) <= 20)
		{
			$this->rememberModel->purgeOldRememberTokens();
		}

		// trigger login event, in case anyone cares
		Events::trigger('login', $user);

		return true;
	}

	/**
	 * Logs a user in based on their ID.
	 *
	 * @param integer $userId
	 *
	 * @return mixed
	 */
	public function loginById(int $userId)
	{
		$user = $this->provider->findById($userId);

		if (empty($user))
		{
			throw AuthenticationException::forInvalidUser();
		}

		return $this->login($user);
	}

	/**
	 * Logs the current user out.
	 *
	 * @return mixed
	 */
	public function logout()
	{
		helper('cookie');

		// Destroy the session data - but ensure a session is still
		// available for flash messages, etc.
		if (isset($_SESSION))
		{
			foreach ($_SESSION as $key => $value)
			{
				$_SESSION[$key] = null;
				unset( $_SESSION[$key] );
			}
		}

		// Regenerate the session ID for a touch of added safety.
		session()->regenerate(true);

		// Take care of any remember me functionality
		$this->rememberModel->purgeRememberTokens($this->user->id ?? null);

		// trigger logout event
		Events::trigger('logout', $this->user);

		$this->user = null;
	}

	/**
	 * Removes any remember-me tokens, if applicable.
	 *
	 * @param integer|null $id ID of user to forget.
	 *
	 * @return void
	 */
	public function forget(?int $id = null)
	{
		if (empty($id))
		{
			if (! $this->loggedIn())
			{
				return;
			}

			$id = $this->user->id;
		}

		$this->rememberModel->purgeRememberTokens($id);
	}

	/**
	 * Returns the current user instance.
	 *
	 * @return \Sparks\Shield\Interfaces\Authenticatable|null
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Record a login attempt
	 *
	 * @param string       $email
	 * @param boolean      $success
	 * @param string|null  $ipAddress
	 * @param integer|null $userID
	 *
	 * @return boolean|integer|string
	 */
	protected function recordLoginAttempt(string $email, bool $success, string $ipAddress = null, int $userID = null)
	{
		return $this->loginModel->insert([
			'ip_address' => $ipAddress,
			'email'      => $email,
			'user_id'    => $userID,
			'date'       => date('Y-m-d H:i:s'),
			'success'    => (int)$success,
		]);
	}

	/**
	 * Generates a timing-attack safe remember me token
	 * and stores the necessary info in the db and a cookie.
	 *
	 * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
	 *
	 * @param integer $userID
	 *
	 * @throws \Exception
	 */
	protected function rememberUser(int $userID)
	{
		$selector  = bin2hex(random_bytes(12));
		$validator = bin2hex(random_bytes(20));
		$expires   = date('Y-m-d H:i:s', time() + $this->config['rememberLength']);

		$token = $selector . ':' . $validator;

		// Store it in the database
		$this->rememberModel->rememberUser($userID, $selector, hash('sha256', $validator), $expires);

		// Save it to the user's browser in a cookie.
		$appConfig = new App();
		$response  = service('response');

		// Create the cookie
		$response->setCookie(
			$this->config['rememberCookieName'],
			$token,                             // Value
			$this->config['rememberLength'],    // # Seconds until it expires
			$appConfig->cookieDomain,
			$appConfig->cookiePath,
			$appConfig->cookiePrefix,
			false,                          // Only send over HTTPS?
			true                            // Hide from Javascript?
		);
	}
}
