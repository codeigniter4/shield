<?php

namespace Sparks\Shield;

use CodeIgniter\Router\RouteCollection;
use Sparks\Shield\Authentication\Authentication;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Authentication\AuthenticatorInterface;
use Sparks\Shield\Interfaces\Authenticatable;

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
	protected $handler;

	/**
	 * @var Authenticatable
	 */
	protected $user;

	/**
	 * @var \Sparks\Shield\Interfaces\UserProvider
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
	public function setHandler(string $handler = null)
	{
		if (! empty($handler))
		{
			$this->handler = $handler;
		}

		return $this;
	}

	/**
	 * Returns the handler name.
	 *
	 * @return string
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Returns the current authentication class.
	 *
	 * @return AuthenticatorInterface
	 */
	public function getAuthenticator()
	{
		return $this->authenticate
			->factory($this->handler);
	}

	/**
	 * Returns the current user, if logged in.
	 *
	 * @return Authenticatable|null
	 */
	public function user()
	{
		return $this->getAuthenticator()->loggedIn()
			? $this->getAuthenticator()->getUser()
			: null;
	}

	/**
	 * Returns the current user's id, if logged in.
	 *
	 * @return mixed|null
	 */
	public function id()
	{
		return ($user = $this->user())
			? $user->getAuthId()
			: null;
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

	/**
	 * Will set the routes in your application to use
	 * the Shield auth routes.
	 *
	 * Usage (in Config/Routes.php):
	 *      - auth()->routes($routes);
	 *      - auth()->routes($routes, ['except' => ['login', 'register']])
	 *
	 * @param \CodeIgniter\Router\RouteCollection $routes
	 * @param array                               $config
	 */
	public function routes(RouteCollection &$routes, array $config = [])
	{
		$authRoutes = config('AuthRoutes')->routes;

		$routes->group('/', ['namespace' => 'Sparks\Shield\Controllers'], function ($routes) use ($authRoutes, $config) {
			foreach ($authRoutes as $name => $row)
			{
				if (! isset($config['except']) || (isset($config['except']) && ! array_key_exists($name, $config['except'])))
				{
					foreach ($row as $params)
					{
						$routes->{$params[0]}($params[1], $params[2]);
					}
				}
			}
		});
	}

	public function authorize($entity, string $permission)
	{
	}

	/**
	 * Returns the Model that is responsible for getting users.
	 *
	 * @return \Sparks\Shield\Interfaces\UserProvider|mixed
	 * @throws \Sparks\Shield\Authentication\AuthenticationException
	 */
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

	/**
	 * Provide magic function-access to handlers to save use
	 * from repeating code here, and to allow them have their
	 * own, additional, features on top of the required ones,
	 * like "remember-me" functionality.
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @throws AuthenticationException
	 */
	public function __call($method, $args)
	{
		$authenticate = $this->authenticate->factory($this->handler);

		if (method_exists($authenticate, $method))
		{
			return $authenticate->{$method}(...$args);
		}
	}
}
