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

namespace CodeIgniter\Shield\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;
use CodeIgniter\Shield\Authentication\Authenticators\HmacSha256;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\HMAC\HmacEncrypter;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Entities\AccessToken;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Exceptions\ValidationException;
use Exception;
use Faker\Generator;
use ReflectionException;

class UserIdentityModel extends BaseModel
{
    protected $primaryKey     = 'id';
    protected $returnType     = UserIdentity::class;
    protected $useSoftDeletes = false;
    protected $allowedFields  = [
        'user_id',
        'type',
        'name',
        'secret',
        'secret2',
        'expires',
        'extra',
        'force_reset',
        'last_used_at',
    ];
    protected $useTimestamps = true;

    protected function initialize(): void
    {
        parent::initialize();

        $this->table = $this->tables['identities'];
    }

    /**
     * Inserts a record
     *
     * @param array|object $data
     *
     * @throws DatabaseException
     */
    public function create($data): void
    {
        $this->disableDBDebug();

        $return = $this->insert($data);

        $this->checkQueryReturn($return);
    }

    /**
     * Creates a new identity for this user with an email/password
     * combination.
     *
     * @phpstan-param array{email: string, password: string} $credentials
     */
    public function createEmailIdentity(User $user, array $credentials): void
    {
        $this->checkUserId($user);

        /** @var Passwords $passwords */
        $passwords = service('passwords');

        $return = $this->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
            'secret'  => $credentials['email'],
            'secret2' => $passwords->hash($credentials['password']),
        ]);

        $this->checkQueryReturn($return);
    }

    private function checkUserId(User $user): void
    {
        if ($user->id === null) {
            throw new LogicException(
                '"$user->id" is null. You should not use the incomplete User object.'
            );
        }
    }

    /**
     * Create an identity with 6 digits code for auth action
     *
     * @phpstan-param array{type: string, name: string, extra: string} $data
     * @param callable $codeGenerator generate secret code
     *
     * @return string secret
     */
    public function createCodeIdentity(
        User $user,
        array $data,
        callable $codeGenerator
    ): string {
        $this->checkUserId($user);

        helper('text');

        // Create an identity for the action
        $maxTry          = 5;
        $data['user_id'] = $user->id;

        while (true) {
            $data['secret'] = $codeGenerator();

            try {
                $this->create($data);

                break;
            } catch (DatabaseException $e) {
                $maxTry--;

                if ($maxTry === 0) {
                    throw $e;
                }
            }
        }

        return $data['secret'];
    }

    /**
     * Generates a new personal access token for the user.
     *
     * @param string   $name   Token name
     * @param string[] $scopes Permissions the token grants
     */
    public function generateAccessToken(User $user, string $name, array $scopes = ['*']): AccessToken
    {
        $this->checkUserId($user);

        helper('text');

        $return = $this->insert([
            'type'    => AccessTokens::ID_TYPE_ACCESS_TOKEN,
            'user_id' => $user->id,
            'name'    => $name,
            'secret'  => hash('sha256', $rawToken = random_string('crypto', 64)),
            'extra'   => serialize($scopes),
        ]);

        $this->checkQueryReturn($return);

        /** @var AccessToken $token */
        $token = $this
            ->asObject(AccessToken::class)
            ->find($this->getInsertID());

        $token->raw_token = $rawToken;

        return $token;
    }

    public function getAccessTokenByRawToken(string $rawToken): ?AccessToken
    {
        return $this
            ->where('type', AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->where('secret', hash('sha256', $rawToken))
            ->asObject(AccessToken::class)
            ->first();
    }

    public function getAccessToken(User $user, string $rawToken): ?AccessToken
    {
        $this->checkUserId($user);

        return $this->where('user_id', $user->id)
            ->where('type', AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->where('secret', hash('sha256', $rawToken))
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Given the ID, returns the given access token.
     *
     * @param int|string $id
     */
    public function getAccessTokenById($id, User $user): ?AccessToken
    {
        $this->checkUserId($user);

        return $this->where('user_id', $user->id)
            ->where('type', AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->where('id', $id)
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * @return AccessToken[]
     */
    public function getAllAccessTokens(User $user): array
    {
        $this->checkUserId($user);

        return $this
            ->where('user_id', $user->id)
            ->where('type', AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->orderBy($this->primaryKey)
            ->asObject(AccessToken::class)
            ->findAll();
    }

    // HMAC
    /**
     * Find and Retrieve the HMAC AccessToken based on Token alone
     *
     * @return ?AccessToken Full HMAC Access Token object
     */
    public function getHmacTokenByKey(string $key): ?AccessToken
    {
        return $this
            ->where('type', HmacSha256::ID_TYPE_HMAC_TOKEN)
            ->where('secret', $key)
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Generates a new personal access token for the user.
     *
     * @param string   $name   Token name
     * @param string[] $scopes Permissions the token grants
     *
     * @throws Exception
     * @throws ReflectionException
     */
    public function generateHmacToken(User $user, string $name, array $scopes = ['*']): AccessToken
    {
        $this->checkUserId($user);

        $encrypter    = new HmacEncrypter();
        $rawSecretKey = $encrypter->generateSecretKey();
        $secretKey    = $encrypter->encrypt($rawSecretKey);

        $return = $this->insert([
            'type'    => HmacSha256::ID_TYPE_HMAC_TOKEN,
            'user_id' => $user->id,
            'name'    => $name,
            'secret'  => bin2hex(random_bytes(16)), // Key
            'secret2' => $secretKey,
            'extra'   => serialize($scopes),
        ]);

        $this->checkQueryReturn($return);

        /** @var AccessToken $token */
        $token = $this
            ->asObject(AccessToken::class)
            ->find($this->getInsertID());

        $token->rawSecretKey = $rawSecretKey;

        return $token;
    }

    /**
     * Retrieve Token object for selected HMAC Token.
     * Note: These tokens are not hashed as they are considered shared secrets.
     *
     * @param User   $user User Object
     * @param string $key  HMAC Key String
     *
     * @return ?AccessToken Full HMAC Access Token
     */
    public function getHmacToken(User $user, string $key): ?AccessToken
    {
        $this->checkUserId($user);

        return $this->where('user_id', $user->id)
            ->where('type', HmacSha256::ID_TYPE_HMAC_TOKEN)
            ->where('secret', $key)
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Given the ID, returns the given access token.
     *
     * @param int|string $id
     * @param User       $user User Object
     *
     * @return ?AccessToken Full HMAC Access Token
     */
    public function getHmacTokenById($id, User $user): ?AccessToken
    {
        $this->checkUserId($user);

        return $this->where('user_id', $user->id)
            ->where('type', HmacSha256::ID_TYPE_HMAC_TOKEN)
            ->where('id', $id)
            ->asObject(AccessToken::class)
            ->first();
    }

    /**
     * Retrieve all HMAC tokes for users
     *
     * @param User $user User object
     *
     * @return AccessToken[]
     */
    public function getAllHmacTokens(User $user): array
    {
        $this->checkUserId($user);

        return $this
            ->where('user_id', $user->id)
            ->where('type', HmacSha256::ID_TYPE_HMAC_TOKEN)
            ->orderBy($this->primaryKey)
            ->asObject(AccessToken::class)
            ->findAll();
    }

    /**
     * Delete any HMAC tokens for the given key.
     *
     * @param User   $user User object
     * @param string $key  HMAC Key
     */
    public function revokeHmacToken(User $user, string $key): void
    {
        $this->checkUserId($user);

        $return = $this->where('user_id', $user->id)
            ->where('type', HmacSha256::ID_TYPE_HMAC_TOKEN)
            ->where('secret', $key)
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * Revokes all access tokens for this user.
     */
    public function revokeAllHmacTokens(User $user): void
    {
        $this->checkUserId($user);

        $return = $this->where('user_id', $user->id)
            ->where('type', HmacSha256::ID_TYPE_HMAC_TOKEN)
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * Used by 'magic-link'.
     */
    public function getIdentityBySecret(string $type, ?string $secret): ?UserIdentity
    {
        if ($secret === null) {
            return null;
        }

        return $this->where('type', $type)
            ->where('secret', $secret)
            ->first();
    }

    /**
     * Returns all identities.
     *
     * @return UserIdentity[]
     */
    public function getIdentities(User $user): array
    {
        $this->checkUserId($user);

        return $this->where('user_id', $user->id)->orderBy($this->primaryKey)->findAll();
    }

    /**
     * @param int[]|string[] $userIds
     *
     * @return UserIdentity[]
     */
    public function getIdentitiesByUserIds(array $userIds): array
    {
        return $this->whereIn('user_id', $userIds)->orderBy($this->primaryKey)->findAll();
    }

    /**
     * Returns the first identity of the type.
     */
    public function getIdentityByType(User $user, string $type): ?UserIdentity
    {
        $this->checkUserId($user);

        return $this->where('user_id', $user->id)
            ->where('type', $type)
            ->orderBy($this->primaryKey)
            ->first();
    }

    /**
     * Returns all identities for the specific types.
     *
     * @param string[] $types
     *
     * @return UserIdentity[]
     */
    public function getIdentitiesByTypes(User $user, array $types): array
    {
        $this->checkUserId($user);

        if ($types === []) {
            return [];
        }

        return $this->where('user_id', $user->id)
            ->whereIn('type', $types)
            ->orderBy($this->primaryKey)
            ->findAll();
    }

    /**
     * Update the last used at date for an identity record.
     */
    public function touchIdentity(UserIdentity $identity): void
    {
        $identity->last_used_at = Time::now()->format('Y-m-d H:i:s');

        $return = $this->save($identity);

        $this->checkQueryReturn($return);
    }

    public function deleteIdentitiesByType(User $user, string $type): void
    {
        $this->checkUserId($user);

        $return = $this->where('user_id', $user->id)
            ->where('type', $type)
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * Delete any access tokens for the given raw token.
     */
    public function revokeAccessToken(User $user, string $rawToken): void
    {
        $this->checkUserId($user);

        $return = $this->where('user_id', $user->id)
            ->where('type', AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->where('secret', hash('sha256', $rawToken))
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * Delete any access tokens for the given secret token.
     */
    public function revokeAccessTokenBySecret(User $user, string $secretToken): void
    {
        $this->checkUserId($user);

        $return = $this->where('user_id', $user->id)
            ->where('type', AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->where('secret', $secretToken)
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * Revokes all access tokens for this user.
     */
    public function revokeAllAccessTokens(User $user): void
    {
        $this->checkUserId($user);

        $return = $this->where('user_id', $user->id)
            ->where('type', AccessTokens::ID_TYPE_ACCESS_TOKEN)
            ->delete();

        $this->checkQueryReturn($return);
    }

    /**
     * Force password reset for multiple users.
     *
     * @param int[]|string[] $userIds
     */
    public function forceMultiplePasswordReset(array $userIds): void
    {
        $this->where(['type' => Session::ID_TYPE_EMAIL_PASSWORD, 'force_reset' => 0]);
        $this->whereIn('user_id', $userIds);
        $this->set('force_reset', 1);
        $return = $this->update();

        $this->checkQueryReturn($return);
    }

    /**
     * Force global password reset.
     * This is useful for enforcing a password reset
     * for ALL users in case of a security breach.
     */
    public function forceGlobalPasswordReset(): void
    {
        $whereFilter = [
            'type'        => Session::ID_TYPE_EMAIL_PASSWORD,
            'force_reset' => 0,
        ];
        $this->where($whereFilter);
        $this->set('force_reset', 1);
        $return = $this->update();

        $this->checkQueryReturn($return);
    }

    /**
     * Override the Model's `update()` method.
     * Throws an Exception when it fails.
     *
     * @param array|int|string|null $id
     * @param array|object|null     $data
     *
     * @return true if the update is successful
     *
     * @throws ValidationException
     */
    public function update($id = null, $data = null): bool
    {
        $result = parent::update($id, $data);

        $this->checkQueryReturn($result);

        return true;
    }

    public function fake(Generator &$faker): UserIdentity
    {
        return new UserIdentity([
            'user_id'      => fake(UserModel::class)->id,
            'type'         => Session::ID_TYPE_EMAIL_PASSWORD,
            'name'         => null,
            'secret'       => $faker->unique()->email(),
            'secret2'      => password_hash('secret', PASSWORD_DEFAULT),
            'expires'      => null,
            'extra'        => null,
            'force_reset'  => false,
            'last_used_at' => null,
        ]);
    }
}
