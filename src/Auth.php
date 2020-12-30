<?php

namespace CodeIgniter\Shield;

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

	public function __construct(Authentication $authenticate)
	{
		$this->authenticate = $authenticate;
	}

	/**
	 * Sets the handler that should be used for this request.
	 *
	 * @param string $handler
	 */
	public function withHandler(string $handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Returns the current user, if logged in.
	 *
	 * @return \CodeIgniter\Shield\User
	 */
	public function user()
	{
		return $this->user;
	}

	/**
	 * Returns the current user's id, if logged in.
	 *
	 * @return |null
	 */
	public function id()
	{
		return $this->user->id ?? null;
	}

	public function authenticate(array $credentials): bool
	{
		$response = $this->authenticate
			->factory($this->handler)
			->authenticate($credentials);

		if ($response->isOk())
		{
			$this->user = $response->user;
		}

		return $response->isOk();
	}

	public function check(array $credentials): bool
	{
		return $this->authenticate
			->factory($this->handler)
			->check($credentials);
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
}
