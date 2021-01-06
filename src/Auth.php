<?php

namespace CodeIgniter\Shield;

use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\User;
use CodeIgniter\Shield\Authentication\Authentication;

class Auth
{
	/**
	 * @var Authentication
	 */
	protected $authenticate;

	protected $authorize;

	/**
	 * The handler to use for this request.
	 *
	 * @var string
	 */
	protected $handler = 'default';

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @var \CodeIgniter\Shield\Interfaces\UserProvider
	 */
	protected $userProvider;

	public function __construct(Authentication $authenticate)
	{
		$this->authenticate = $authenticate->setProvider($this->getProvider());
	}

	/**
	 * Sets the handler that should be used for this request.
	 *
	 * @param string|null $handler
	 */
	public function withHandler(string $handler = null)
	{
		$this->handler = $handler;

		return $this;
	}

	/**
	 * Returns the current user, if logged in.
	 *
	 * @return \CodeIgniter\Shield\User
	 */
	public function user()
	{
		return $this->authenticate
		->factory($this->handler)
		->getUser();
	}

	/**
	 * Returns the current user's id, if logged in.
	 *
	 * @return |null
	 */
	public function id()
	{
		return $this->authenticate
		   ->factory($this->handler)
		   ->getUser()
		   ->id
			   ?? null;
	}

	public function authenticate(array $credentials)
	{
		$response = $this->authenticate
			->factory($this->handler)
			->attempt($credentials);

		if ($response->isOk())
		{
			$this->user = $response->extraInfo();
		}

		return $response;
	}

	public function check(array $credentials): bool
	{
		return $this->authenticate
			->factory($this->handler)
			->check($credentials);
	}

	public function loggedIn(): bool
	{
		return $this->authenticate
			->factory($this->handler)
			->loggedIn();
	}

	public function login(User $user)
	{
		return $this->authenticate
			->factory($this->handler)
			->login($user);
	}

	public function loginById(int $userId)
	{
		return $this->authenticate
			->factory($this->handler)
			->loginById($userId);
	}

	public function logout()
	{
		return $this->authenticate
			->factory($this->handler)
			->logout();
	}

	public function forget()
	{
		return $this->authenticate
			->factory($this->handler)
			->forget();
	}

	public function routes(array $config = null)
	{
	}

	public function authorize($entity, string $permission)
	{
	}

	public function getProvider()
	{
		if ($this->userProvider !== null)
		{
			return $this->userProvider;
		}

		$config = config('Auth');

		if (! property_exists($config, 'userProvider'))
		{
			throw AuthenticationException::forUnknownUserProvider();
		}

		$className          = $config->userProvider;
		$this->userProvider = new $className();

		return $this->userProvider;
	}
}
