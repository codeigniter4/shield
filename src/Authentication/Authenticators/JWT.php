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

namespace CodeIgniter\Shield\Authentication\Authenticators;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Authentication\AuthenticatorInterface;
use CodeIgniter\Shield\Authentication\JWTManager;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\TokenLoginModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Result;
use InvalidArgumentException;
use stdClass;

/**
 * Stateless JWT Authenticator
 */
class JWT implements AuthenticatorInterface
{
    /**
     * @var string Special ID Type.
     *             This Authenticator is stateless, so no `auth_identities` record.
     */
    public const ID_TYPE_JWT = 'jwt';

    /**
     * The persistence engine
     */
    protected UserModel $provider;

    protected ?User $user = null;
    protected JWTManager $jwtManager;
    protected TokenLoginModel $tokenLoginModel;
    protected ?stdClass $payload = null;

    /**
     * @var string The key group. The array key of Config\AuthJWT::$keys.
     */
    protected $keyset = 'default';

    public function __construct(UserModel $provider)
    {
        $this->provider = $provider;

        $this->jwtManager      = service('jwtmanager');
        $this->tokenLoginModel = model(TokenLoginModel::class);
    }

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @param array{token?: string} $credentials
     */
    public function attempt(array $credentials): Result
    {
        /** @var AuthJWT $config */
        $config = config('AuthJWT');

        /** @var IncomingRequest $request */
        $request = service('request');

        $ipAddress = $request->getIPAddress();
        $userAgent = (string) $request->getUserAgent();

        $result = $this->check($credentials);

        if (! $result->isOK()) {
            if ($config->recordLoginAttempt >= Auth::RECORD_LOGIN_ATTEMPT_FAILURE) {
                // Record a failed login attempt.
                $this->tokenLoginModel->recordLoginAttempt(
                    self::ID_TYPE_JWT,
                    $credentials['token'] ?? '',
                    false,
                    $ipAddress,
                    $userAgent
                );
            }

            return $result;
        }

        $user = $result->extraInfo();

        if ($user->isBanned()) {
            if ($config->recordLoginAttempt >= Auth::RECORD_LOGIN_ATTEMPT_FAILURE) {
                // Record a banned login attempt.
                $this->tokenLoginModel->recordLoginAttempt(
                    self::ID_TYPE_JWT,
                    'sha256:' . hash('sha256', $credentials['token'] ?? ''),
                    false,
                    $ipAddress,
                    $userAgent,
                    $user->id
                );
            }

            $this->user = null;

            return new Result([
                'success' => false,
                'reason'  => $user->getBanMessage() ?? lang('Auth.bannedUser'),
            ]);
        }

        $this->login($user);

        if ($config->recordLoginAttempt === Auth::RECORD_LOGIN_ATTEMPT_ALL) {
            // Record a successful login attempt.
            $this->tokenLoginModel->recordLoginAttempt(
                self::ID_TYPE_JWT,
                'sha256:' . hash('sha256', $credentials['token']),
                true,
                $ipAddress,
                $userAgent,
                $this->user->id
            );
        }

        return $result;
    }

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     *
     * In this case, $credentials has only a single valid value: token,
     * which is the plain text token to return.
     *
     * @param array{token?: string} $credentials
     */
    public function check(array $credentials): Result
    {
        if (! array_key_exists('token', $credentials) || $credentials['token'] === '') {
            return new Result([
                'success' => false,
                'reason'  => lang(
                    'Auth.noToken',
                    [config('AuthJWT')->authenticatorHeader]
                ),
            ]);
        }

        // Check JWT
        try {
            $this->payload = $this->jwtManager->parse($credentials['token'], $this->keyset);
        } catch (RuntimeException $e) {
            return new Result([
                'success' => false,
                'reason'  => $e->getMessage(),
            ]);
        }

        $userId = $this->payload->sub ?? null;

        if ($userId === null) {
            return new Result([
                'success' => false,
                'reason'  => 'Invalid JWT: no user_id',
            ]);
        }

        // Find User
        $user = $this->provider->findById($userId);

        if ($user === null) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.invalidUser'),
            ]);
        }

        return new Result([
            'success'   => true,
            'extraInfo' => $user,
        ]);
    }

    /**
     * Checks if the user is currently logged in.
     * Since AccessToken usage is inherently stateless,
     * it runs $this->attempt on each usage.
     */
    public function loggedIn(): bool
    {
        if ($this->user !== null) {
            return true;
        }

        /** @var IncomingRequest $request */
        $request = service('request');

        /** @var AuthJWT $config */
        $config = config('AuthJWT');

        return $this->attempt([
            'token' => $request->getHeaderLine($config->authenticatorHeader),
        ])->isOK();
    }

    /**
     * Logs the given user in by saving them to the class.
     */
    public function login(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Logs a user in based on their ID.
     *
     * @param int|string $userId
     *
     * @throws AuthenticationException
     */
    public function loginById($userId): void
    {
        $user = $this->provider->findById($userId);

        if ($user === null) {
            throw AuthenticationException::forInvalidUser();
        }

        $this->login($user);
    }

    /**
     * Logs the current user out.
     */
    public function logout(): void
    {
        $this->user = null;
    }

    /**
     * Returns the currently logged in user.
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
                __METHOD__ . '() requires logged in user before calling.'
            );
        }

        $this->user->last_active = Time::now();

        $this->provider->save($this->user);
    }

    /**
     * @param string $keyset The key group. The array key of Config\AuthJWT::$keys.
     */
    public function setKeyset($keyset): void
    {
        $this->keyset = $keyset;
    }

    /**
     * Returns payload
     */
    public function getPayload(): ?stdClass
    {
        return $this->payload;
    }
}
