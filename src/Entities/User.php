<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\Traits\HasAccessTokens;
use CodeIgniter\Shield\Authorization\Traits\Authorizable;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Traits\Activatable;
use CodeIgniter\Shield\Traits\Bannable;
use CodeIgniter\Shield\Traits\Resettable;

/**
 * @property string|null         $email
 * @property int|string|null     $id
 * @property UserIdentity[]|null $identities
 * @property Time|null           $last_active
 * @property string|null         $password
 * @property string|null         $password_hash
 * @property string|null         $username
 */
class User extends Entity
{
    use Authorizable;
    use HasAccessTokens;
    use Resettable;
    use Activatable;
    use Bannable;

    /**
     * @var UserIdentity[]|null
     */
    private ?array $identities = null;

    private ?string $email         = null;
    private ?string $password      = null;
    private ?string $password_hash = null;

    /**
     * @var string[]
     * @phpstan-var list<string>
     * @psalm-var list<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'last_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'          => '?integer',
        'active'      => 'int_bool',
        'permissions' => 'array',
        'groups'      => 'array',
    ];

    /**
     * Returns the first identity of the given $type for this user.
     *
     * @param string $type See const ID_TYPE_* in Authenticator.
     *                     'email_2fa'|'email_activate'|'email_password'|'magic-link'|'access_token'
     */
    public function getIdentity(string $type): ?UserIdentity
    {
        $identities = $this->getIdentities($type);

        return count($identities) ? array_shift($identities) : null;
    }

    /**
     * ensures that all of the user's identities are loaded
     * into the instance for faster access later.
     */
    private function populateIdentities(): void
    {
        if ($this->identities === null) {
            /** @var UserIdentityModel $identityModel */
            $identityModel = model(UserIdentityModel::class);

            $this->identities = $identityModel->getIdentities($this);
        }
    }

    /**
     * Accessor method for this user's UserIdentity objects.
     * Will populate if they don't exist.
     *
     * @param string $type 'all' returns all identities.
     *
     * @return UserIdentity[]
     */
    public function getIdentities(string $type = 'all'): array
    {
        $this->populateIdentities();

        if ($type === 'all') {
            return $this->identities;
        }

        $identities = [];

        foreach ($this->identities as $identity) {
            if ($identity->type === $type) {
                $identities[] = $identity;
            }
        }

        return $identities;
    }

    /**
     * Creates a new identity for this user with an email/password
     * combination.
     *
     * @phpstan-param array{email: string, password: string} $credentials
     */
    public function createEmailIdentity(array $credentials): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->createEmailIdentity($this, $credentials);

        // Ensure we will reload all identities
        $this->identities = null;
    }

    /**
     * Returns the user's Email/Password identity.
     */
    public function getEmailIdentity(): ?UserIdentity
    {
        return $this->getIdentity(Session::ID_TYPE_EMAIL_PASSWORD);
    }

    /**
     * If $email, $password, or $password_hash have been updated,
     * will update the user's email identity record with the
     * correct values.
     */
    public function saveEmailIdentity(): bool
    {
        if (empty($this->email) && empty($this->password) && empty($this->password_hash)) {
            return true;
        }

        $identity = $this->getEmailIdentity();
        if ($identity === null) {
            // Ensure we reload all identities
            $this->identities = null;

            $this->createEmailIdentity([
                'email'    => $this->email,
                'password' => '',
            ]);

            $identity = $this->getEmailIdentity();
        }

        if (! empty($this->email)) {
            $identity->secret = $this->email;
        }

        if (! empty($this->password)) {
            $identity->secret2 = service('passwords')->hash($this->password);
        }

        if (! empty($this->password_hash) && empty($this->password)) {
            $identity->secret2 = $this->password_hash;
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        try {
            /** @throws DataException */
            $identityModel->save($identity);
        } catch (DataException $e) {
            // There may be no data to update.
            $messages = [
                lang('Database.emptyDataset', ['insert']),
                lang('Database.emptyDataset', ['update']),
            ];
            if (in_array($e->getMessage(), $messages, true)) {
                return true;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Update the last used at date for an identity record.
     */
    public function touchIdentity(UserIdentity $identity): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->touchIdentity($identity);
    }

    /**
     * Accessor method to grab the user's email address.
     * Will cache it in $this->email, since it has
     * to hit the database the first time to get it, most likely.
     */
    public function getEmail(): ?string
    {
        if ($this->email === null) {
            $this->email = $this->getEmailIdentity()->secret ?? null;
        }

        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function setPasswordHash(string $hash): User
    {
        $this->password_hash = $hash;

        return $this;
    }

    /**
     * Accessor method to grab the user's password hash.
     * Will cache it in $this->attributes, since it has
     * to hit the database the first time to get it, most likely.
     */
    public function getPasswordHash(): ?string
    {
        if ($this->password_hash === null) {
            $this->password_hash = $this->getEmailIdentity()->secret2 ?? null;
        }

        return $this->password_hash;
    }

    /**
     * Returns the previous login information for this user
     */
    public function previousLogin(): ?Login
    {
        /** @var LoginModel $logins */
        $logins = model(LoginModel::class);

        return $logins->previousLogin($this);
    }

    /**
     * Returns the last login information for this user as
     */
    public function lastLogin(): ?Login
    {
        /** @var LoginModel $logins */
        $logins = model(LoginModel::class);

        return $logins->lastLogin($this);
    }
}
