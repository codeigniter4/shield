<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield;

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Shield\Authentication\Authentication;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Config\Auth as AuthConfig;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Facade for Authentication
 *
 * @method Result    attempt(array $credentials)
 * @method Result    check(array $credentials)
 * @method bool      checkAction(string $token, string $type) [Session]
 * @method void      forget(?User $user = null)               [Session]
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
    public const SHIELD_VERSION = '1.0.0-beta.8';

    protected AuthConfig $config;
    protected ?Authentication $authenticate = null;

    /**
     * The Authenticator alias to use for this request.
     */
    protected ?string $alias = null;

    protected ?UserModel $userProvider = null;

    public function __construct(AuthConfig $config)
    {
        $this->config = $config;
    }

    protected function ensureAuthentication(): void
    {
        if ($this->authenticate !== null) {
            return;
        }

        $authenticate = new Authentication($this->config);
        $authenticate->setProvider($this->getProvider());

        $this->authenticate = $authenticate;
    }

    /**
     * Sets the Authenticator alias that should be used for this request.
     *
     * @return $this
     */
    public function setAuthenticator(?string $alias = null): self
    {
        if ($alias !== null) {
            $this->alias = $alias;
        }

        return $this;
    }

    /**
     * Returns the current authentication class.
     */
    public function getAuthenticator(): AuthenticatorInterface
    {
        $this->ensureAuthentication();

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
        $user = $this->user();

        return ($user !== null) ? $user->id : null;
    }

    public function authenticate(array $credentials): Result
    {
        $this->ensureAuthentication();

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

        $className          = $this->config->userProvider;
        $this->userProvider = new $className();

        return $this->userProvider;
    }

    /**
     * Provide magic function-access to Authenticators to save use
     * from repeating code here, and to allow them to have their
     * own, additional, features on top of the required ones,
     * like "remember-me" functionality.
     *
     * @param string[] $args
     *
     * @throws AuthenticationException
     */
    public function __call(string $method, array $args)
    {
        $this->ensureAuthentication();

        $authenticator = $this->authenticate->factory($this->alias);

        if (method_exists($authenticator, $method)) {
            return $authenticator->{$method}(...$args);
        }
    }
}
