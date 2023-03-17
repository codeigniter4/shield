<?php

declare(strict_types=1);

namespace CodeIgniter\Shield;

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

/**
 * @method Result    attempt(array $credentials)
 * @method Result    check(array $credentials)
 * @method bool      checkAction(string $token, string $type) [Session]
 * @method User|null getUser()
 * @method bool      loggedIn()
 * @method bool      login(User $user)
 * @method void      loginById($userId)
 * @method bool      logout()
 * @method void      recordActiveDate()
 * @method $this     remember(bool $shouldRemember = true)    [Session]
 */
class Auth
{
    /**
     * The current version of CodeIgniter Shield
     */
    public const SHIELD_VERSION = '1.0.0-beta.5';

    protected Authentication $authenticate;

    /**
     * The Authenticator alias to use for this request.
     */
    protected ?string $alias = null;

    protected ?UserModel $userProvider = null;

    public function __construct(Authentication $authenticate)
    {
        $this->authenticate = $authenticate->setProvider($this->getProvider());
    }

    /**
     * Sets the Authenticator alias that should be used for this request.
     *
     * @return $this
     */
    public function setAuthenticator(?string $alias = null): self
    {
        if (! empty($alias)) {
            $this->alias = $alias;
        }

        return $this;
    }

    /**
     * Returns the current authentication class.
     */
    public function getAuthenticator(): AuthenticatorInterface
    {
        return $this->authenticate
            ->factory($this->alias);
    }

    /**
     * Returns the current user, if logged in.
     */
    public function user(): ?User
    {
        return $this->getAuthenticator()->loggedIn()
            ? $this->getAuthenticator()->getUser()
            : null;
    }

    /**
     * Returns the current user's id, if logged in.
     *
     * @return int|string|null
     */
    public function id()
    {
        return ($user = $this->user())
            ? $user->id
            : null;
    }

    public function authenticate(array $credentials): Result
    {
        return $this->authenticate
            ->factory($this->alias)
            ->attempt($credentials);
    }

    /**
     * Will set the routes in your application to use
     * the Shield auth routes.
     *
     * Usage (in Config/Routes.php):
     *      - auth()->routes($routes);
     *      - auth()->routes($routes, ['except' => ['login', 'register']])
     */
    public function routes(RouteCollection &$routes, array $config = []): void
    {
        $authRoutes = config('AuthRoutes')->routes;

        $routes->group('/', ['namespace' => 'CodeIgniter\Shield\Controllers'], static function (RouteCollection $routes) use ($authRoutes, $config): void {
            foreach ($authRoutes as $name => $row) {
                if (! isset($config['except']) || ! in_array($name, $config['except'], true)) {
                    foreach ($row as $params) {
                        $options = isset($params[3])
                            ? ['as' => $params[3]]
                            : null;
                        $routes->{$params[0]}($params[1], $params[2], $options);
                    }
                }
            }
        });
    }

    /**
     * Returns the Model that is responsible for getting users.
     *
     * @throws AuthenticationException
     */
    public function getProvider(): UserModel
    {
        if ($this->userProvider !== null) {
            return $this->userProvider;
        }

        /** @var \CodeIgniter\Shield\Config\Auth $config */
        $config = config('Auth');

        if (! property_exists($config, 'userProvider')) {
            throw AuthenticationException::forUnknownUserProvider();
        }

        $className          = $config->userProvider;
        $this->userProvider = new $className();

        return $this->userProvider;
    }

    /**
     * Provide magic function-access to Authenticators to save use
     * from repeating code here, and to allow them have their
     * own, additional, features on top of the required ones,
     * like "remember-me" functionality.
     *
     * @param string[] $args
     *
     * @throws AuthenticationException
     */
    public function __call(string $method, array $args)
    {
        $authenticate = $this->authenticate->factory($this->alias);

        if (method_exists($authenticate, $method)) {
            return $authenticate->{$method}(...$args);
        }
    }
}
