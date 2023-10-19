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
use CodeIgniter\Shield\Authentication\HMAC\HmacEncrypter;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException;
use CodeIgniter\Shield\Models\TokenLoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Result;

class HmacSha256 implements AuthenticatorInterface
{
    public const ID_TYPE_HMAC_TOKEN = 'hmac_sha256';

    /**
     * The persistence engine
     */
    protected UserModel $provider;

    protected ?User $user = null;
    protected TokenLoginModel $loginModel;

    public function __construct(UserModel $provider)
    {
        $this->provider = $provider;

        $this->loginModel = model(TokenLoginModel::class);
    }

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @throws AuthenticationException
     */
    public function attempt(array $credentials): Result
    {
        $config = config('AuthToken');

        /** @var IncomingRequest $request */
        $request = service('request');

        $ipAddress = $request->getIPAddress();
        $userAgent = (string) $request->getUserAgent();

        $result = $this->check($credentials);

        if (! $result->isOK()) {
            if ($config->recordLoginAttempt >= Auth::RECORD_LOGIN_ATTEMPT_FAILURE) {
                // Record all failed login attempts.
                $this->loginModel->recordLoginAttempt(
                    self::ID_TYPE_HMAC_TOKEN,
                    $credentials['token'] ?? '',
                    false,
                    $ipAddress,
                    $userAgent
                );
            }

            return $result;
        }

        $user  = $result->extraInfo();
        $token = $user->getHmacToken($this->getHmacKeyFromToken());

        if ($user->isBanned()) {
            if ($config->recordLoginAttempt >= Auth::RECORD_LOGIN_ATTEMPT_FAILURE) {
                // Record a banned login attempt.
                $this->loginModel->recordLoginAttempt(
                    self::ID_TYPE_HMAC_TOKEN,
                    $token->name ?? '',
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

        $user = $user->setHmacToken($token);

        $this->login($user);

        if ($config->recordLoginAttempt === Auth::RECORD_LOGIN_ATTEMPT_ALL) {
            // Record a successful login attempt.
            $this->loginModel->recordLoginAttempt(
                self::ID_TYPE_HMAC_TOKEN,
                $token->name ?? '',
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
     */
    public function check(array $credentials): Result
    {
        if (! array_key_exists('token', $credentials) || $credentials['token'] === '') {
            return new Result([
                'success' => false,
                'reason'  => lang(
                    'Auth.noToken',
                    [config('AuthToken')->authenticatorHeader['hmac']]
                ),
            ]);
        }

        if (strpos($credentials['token'], 'HMAC-SHA256') === 0) {
            $credentials['token'] = trim(substr($credentials['token'], 11)); // HMAC-SHA256
        }

        // Extract UserToken and HMACSHA256 Signature from Authorization token
        [$userToken, $signature] = $this->getHmacAuthTokens($credentials['token']);

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $token = $identityModel->getHmacTokenByKey($userToken);

        if ($token === null) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badToken'),
            ]);
        }

        $encrypter = new HmacEncrypter();
        $secretKey = $encrypter->decrypt($token->secret2);

        // Check signature...
        $hash = hash_hmac('sha256', $credentials['body'], $secretKey);
        if ($hash !== $signature) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badToken'),
            ]);
        }

        assert($token->last_used_at instanceof Time || $token->last_used_at === null);

        // Hasn't been used in a long time
        if (
            isset($token->last_used_at)
            && $token->last_used_at->isBefore(
                Time::now()->subSeconds(config('AuthToken')->unusedTokenLifetime)
            )
        ) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.oldToken'),
            ]);
        }

        $token->last_used_at = Time::now()->format('Y-m-d H:i:s');

        if ($token->hasChanged()) {
            $identityModel->save($token);
        }

        // Ensure the token is set as the current token
        $user = $token->user();
        $user->setHmacToken($token);

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
        if (isset($this->user)) {
            return true;
        }

        /** @var IncomingRequest $request */
        $request = service('request');

        return $this->attempt([
            'token' => $request->getHeaderLine(
                config('AuthToken')->authenticatorHeader['hmac']
            ),
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
     * @param int|string $userId User ID
     *
     * @throws AuthenticationException
     */
    public function loginById($userId): void
    {
        $user = $this->provider->findById($userId);

        if ($user === null) {
            throw AuthenticationException::forInvalidUser();
        }

        $user->setHmacToken(
            $user->getHmacToken($this->getHmacKeyFromToken())
        );

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
     * Returns the currently logged-in user.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Returns the Full HMAC Authorization token from the Authorization header
     *
     * @return ?string Trimmed Authorization Token from Header
     */
    public function getFullHmacToken(): ?string
    {
        /** @var IncomingRequest $request */
        $request = service('request');

        $header = $request->getHeaderLine(config('AuthToken')->authenticatorHeader['hmac']);

        if ($header === '') {
            return null;
        }

        return trim(substr($header, 11));   // 'HMAC-SHA256'
    }

    /**
     * Get Key and HMAC hash from Auth token
     *
     * @param ?string $fullToken Full Token
     *
     * @return ?array [key, hmacHash]
     */
    public function getHmacAuthTokens(?string $fullToken = null): ?array
    {
        if (! isset($fullToken)) {
            $fullToken = $this->getFullHmacToken();
        }

        if (isset($fullToken)) {
            return preg_split('/:/', $fullToken, -1, PREG_SPLIT_NO_EMPTY);
        }

        return null;
    }

    /**
     * Retrieve the key from the Auth token
     *
     * @return ?string HMAC token key
     */
    public function getHmacKeyFromToken(): ?string
    {
        [$key, $secretKey] = $this->getHmacAuthTokens();

        return $key;
    }

    /**
     * Retrieve the HMAC Hash from the Auth token
     *
     * @return ?string HMAC Hash
     */
    public function getHmacHashFromToken(): ?string
    {
        [$key, $hash] = $this->getHmacAuthTokens();

        return $hash;
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

        $this->provider->updateActiveDate($this->user);
    }
}
