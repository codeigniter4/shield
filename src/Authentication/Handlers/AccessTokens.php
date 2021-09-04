<?php

namespace Sparks\Shield\Authentication\Handlers;

use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Authentication\AuthenticatorInterface;
use Sparks\Shield\Config\Auth as AuthConfig;
use Sparks\Shield\Entities\AccessToken;
use Sparks\Shield\Interfaces\Authenticatable;
use Sparks\Shield\Models\AccessTokenModel;
use Sparks\Shield\Models\LoginModel;
use Sparks\Shield\Models\UserIdentityModel;
use Sparks\Shield\Result;

class AccessTokens implements AuthenticatorInterface
{
	/**
	 * @var AuthConfig
	 */
	protected $config;

	/**
	 * The persistence engine
	 */
	protected $provider;

	/**
	 * @var Authenticatable|null
	 */
	protected $user;

	/**
	 * @var LoginModel
	 */
	protected $loginModel;

	public function __construct(AuthConfig $config, $provider)
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
	 * @throws AuthenticationException
	 *
	 * @return mixed
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

		$user = $user->setAccessToken(
			$user->getAccessToken($this->getBearerToken())
		);

		$this->login($user);

		$this->loginModel->recordLoginAttempt('token: ' . ($credentials['token'] ?? ''), true, $ipAddress, $this->user->getAuthId());

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

		if (strpos($credentials['token'], 'Bearer') === 0)
		{
			$credentials['token'] = trim(substr($credentials['token'], 6));
		}

		$identities = model(UserIdentityModel::class);
		$token      = $identities
			->where('type', 'access_token')
			->where('secret', hash('sha256', $credentials['token']))
			->asObject(AccessToken::class)
			->first();

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
	 * it runs $this->attempt on each usage.
	 *
	 * @return boolean
	 */
	public function loggedIn(): bool
	{
		if (! empty($this->user))
		{
			return true;
		}

		return $this->attempt([
			'token' => service('request')->getHeaderLine('Authorization'),
		])->isOK();
	}

	/**
	 * Logs the given user in by saving them to the class.
	 * Since AccessTokens are stateless, $remember has no functionality.
	 *
	 * @param Authenticatable $user
	 * @param boolean         $remember
	 *
	 * @return bool
	 */
	public function login(Authenticatable $user, bool $remember = false)
	{
		$this->user = $user;

		return true;
	}

	/**
	 * Logs a user in based on their ID.
	 *
	 * @param integer $userId
	 * @param boolean $remember
	 *
	 * @throws AuthenticationException
	 *
	 * @return bool
	 */
	public function loginById(int $userId, bool $remember = false)
	{
		$user = $this->provider->findById($userId);

		if (empty($user))
		{
			throw AuthenticationException::forInvalidUser();
		}

		$user->setAccessToken(
			$user->getAccessToken($this->getBearerToken())
		);

		return $this->login($user, $remember);
	}

	/**
	 * Logs the current user out.
	 *
	 * @return bool
	 */
	public function logout()
	{
		$this->user = null;

		return true;
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

	/**
	 * Returns the Bearer token from the Authorization header
	 *
	 * @return string|null
	 */
	public function getBearerToken()
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
