<?php

namespace Sparks\Shield\Authentication\Handlers;

use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Authentication\AuthenticatorInterface;
use Sparks\Shield\Entities\AccessToken;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Interfaces\Authenticatable;
use Sparks\Shield\Models\LoginModel;
use Sparks\Shield\Models\UserIdentityModel;
use Sparks\Shield\Result;

class AccessTokens implements AuthenticatorInterface
{
    /**
     * The persistence engine
     */
    protected $provider;

    /**
     * AccessTokens requires HasAccessTokens trait but there is no interface
     * for this so the workaround is restricting this class to work with
     * the native User instead of Authenticatable
     *
     * @todo Fix interface issue described above
     */
    protected ?User $user = null;

    protected LoginModel $loginModel;

    public function __construct($provider)
    {
        $this->provider = $provider;

        $this->loginModel = model(LoginModel::class); // @phpstan-ignore-line
    }

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @throws AuthenticationException
     */
    public function attempt(array $credentials): Result
    {
        $request   = service('request');
        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent();

        $result = $this->check($credentials);

        if (! $result->isOK()) {
            // Always record a login attempt, whether success or not.
            $this->loginModel->recordLoginAttempt(
                'token: ' . ($credentials['token'] ?? ''),
                false,
                $ipAddress,
                $userAgent
            );

            return $result;
        }

        $user = $result->extraInfo();

        $user = $user->setAccessToken(
            $user->getAccessToken($this->getBearerToken())
        );

        $this->login($user);

        $this->loginModel->recordLoginAttempt(
            'token: ' . ($credentials['token'] ?? ''),
            true,
            $ipAddress,
            $userAgent,
            $this->user->getAuthId()
        );

        return $result;
    }

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     *
     * In this case, $credentials has only a single valid value: token,
     * which is the plain text token to return.
     *
     * @return Result
     */
    public function check(array $credentials)
    {
        if (! array_key_exists('token', $credentials) || empty($credentials['token'])) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.noToken'),
            ]);
        }

        if (strpos($credentials['token'], 'Bearer') === 0) {
            $credentials['token'] = trim(substr($credentials['token'], 6));
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $token = $identityModel->getAccessTokenByRawToken($credentials['token']);

        if ($token === null) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badToken'),
            ]);
        }

        // Hasn't been used in a long time
        if (
            $token->last_used_at
            && $token->last_used_at->isBefore(Time::now()->subSeconds(config('Auth')->unusedTokenLifetime))
        ) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.oldToken'),
            ]);
        }

        $token->last_used_at = Time::now()->toDateTimeString();
        $identityModel->save($token);

        // Ensure the token is set as the current token
        $user = $token->user();
        $user->setAccessToken($token);

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
        if (! empty($this->user)) {
            return true;
        }

        return $this->attempt([
            'token' => service('request')->getHeaderLine(config('Auth')->authenticatorHeader['tokens']),
        ])->isOK();
    }

    /**
     * Logs the given user in by saving them to the class.
     *
     * @param User $user
     */
    public function login(Authenticatable $user): bool
    {
        $this->user = $user;

        return true;
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

        if (empty($user)) {
            throw AuthenticationException::forInvalidUser();
        }

        $user->setAccessToken(
            $user->getAccessToken($this->getBearerToken())
        );

        $this->login($user);
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
     * @return mixed
     */
    public function forget(?int $id)
    {
        // Nothing to do here...
    }

    /**
     * Returns the currently logged in user.
     *
     * @return User|null
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

        $header = $request->getHeaderLine(config('Auth')->authenticatorHeader['tokens']);

        if (empty($header)) {
            return null;
        }

        return trim(substr($header, 6));   // 'Bearer'
    }

    /**
     * Updates the user's last active date.
     *
     * @return mixed
     */
    public function recordActive()
    {
        if (! $this->user instanceof User) {
            throw new InvalidArgumentException(self::class . '::recordActive() requires logged in user before calling.');
        }

        $this->user->last_active = Time::now();

        $this->provider->save($this->user);
    }
}
