<?php

namespace CodeIgniter\Shield\Authentication\Handlers;

use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Interfaces\Authenticatable;
use CodeIgniter\Shield\Models\AccessTokenModel;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Result;

class AccessTokens implements AuthenticatorInterface
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
	 * @var \CodeIgniter\Shield\Interfaces\Authenticatable
	 */
	protected $user;

	/**
	 * @var \CodeIgniter\Shield\Models\LoginModel
	 */
	protected $loginModel;

	public function __construct(Auth $config, $provider)
	{
		$this->config     = $config;
		$this->provider   = $provider;
		$this->loginModel = model(LoginModel::class);
	}

	/**
	 * Attempts to authenticate a user with the given $credentials.
	 * Logs the user in with a successful check.
	 *
	 * @param array   $credentials
	 * @param boolean $remember
	 *
	 * @return mixed
	 * @throws \CodeIgniter\Shield\Authentication\AuthenticationException
	 */
	public function attempt(array $credentials, bool $remember = false)
	{
		$ipAddress = service('request')->getIPAddress();
		$result    = $this->check($credentials);

		if (! $result->isOK())
		{
			// Always record a login attempt, whether success or not.
			$this->loginModel->recordLoginAttempt('token: ' . ($credentials['token'] ?? ''), false, $ipAddress, null);

			return $result;
		}

		$user = $result->extraInfo();
		$user->withAccessToken(
			$user->getAccessToken($this->getBearerToken())
		);

		$this->login($user);

		$this->loginModel->recordLoginAttempt('token: ' . ($credentials['token'] ?? ''), true, $ipAddress, $this->user->id);

		return $result;
	}

	/**
	 * Checks a user's $credentials to see if they match an
	 * existing user.
	 *
	 * In this case, $credentials has only a single valid value: token,
	 * which is the plain text token to return.
	 *
	 * @param array $credentials
	 *
	 * @return Result
	 */
	public function check(array $credentials)
	{
		if (! array_key_exists('token', $credentials) || empty($credentials['token']))
		{
			return new Result([
				'success' => false,
				'reason'  => lang('Auth.noToken'),
			]);
		}

		$tokens = model(AccessTokenModel::class);
		$token  = $tokens->where('token', hash('sha256', $credentials['token']))->first();

		if ($token === null)
		{
			return new Result([
				'success' => false,
				'reason'  => lang('Auth.badToken'),
			]);
		}

		return new Result([
			'success'   => true,
			'extraInfo' => $token->user(),
		]);
	}

	/**
	 * Checks if the user is currently logged in.
	 * Since AccessToken usage is inherently stateless,
	 * returns simply whether a user has been
	 * authenticated or not.
	 *
	 * @return boolean
	 */
	public function loggedIn(): bool
	{
		return $this->user instanceof Authenticatable;
	}

	/**
	 * Logs the given user in by saving them to the class.
	 * Since AccessTokens are stateless, $remember has no functionality.
	 *
	 * @param Authenticatable $user
	 * @param boolean         $remember
	 *
	 * @return mixed
	 */
	public function login(Authenticatable $user, bool $remember = false)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Logs a user in based on their ID.
	 *
	 * @param integer $userId
	 * @param boolean $remember
	 *
	 * @return mixed
	 */
	public function loginById(int $userId, bool $remember = false)
	{
		$user = $this->provider->findById($userId);

		if (empty($user))
		{
			throw AuthenticationException::forInvalidUser();
		}

		$user->withAccessToken(
			$user->getAccessToken($this->getBearerToken())
		);

		return $this->login($user, $remember);
	}

	/**
	 * Logs the current user out.
	 *
	 * @return mixed
	 */
	public function logout()
	{
		$this->user = null;
	}

	/**
	 * Removes any remember-me tokens, if applicable.
	 *
	 * @param integer|null $id
	 *
	 * @return mixed
	 */
	public function forget(?int $id)
	{
		// Nothing to do here...
	}

	/**
	 * Returns the currently logged in user.
	 *
	 * @return Authenticatable|null
	 */
	public function getUser()
	{
		return $this->user;
	}

	protected function getBearerToken()
	{
		$request = service('request');

		$header = $request->getHeaderLine('Authorization');

		if (empty($header))
		{
			return null;
		}

		return trim(substr($header, 6));   // 'Bearer'
	}
}
