<?php

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\Shield\Authentication\Passwords;
use CodeIgniter\Shield\Authentication\Traits\HasAccessTokens;
use CodeIgniter\Shield\Authorization\Traits\Authorizable;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;

class User extends Entity
{
    use Authorizable;
    use HasAccessTokens;

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
        'active'           => 'boolean',
        'force_pass_reset' => 'boolean',
        'permissions'      => 'array',
        'groups'           => 'array',
    ];

    /**
     * Returns the first identity of the given $type for this user.
     *
     * @phpstan-param 'email_2fa'|'email_activate'|'email_password' $type
     */
    public function getIdentity(string $type): ?UserIdentity
    {
        $identities = $this->getIdentities($type);

        return count($identities) ? array_shift($identities) : null;
    }

    private function populateIdentities(): void
    {
        if ($this->identities === null) {
            /** @var UserIdentityModel $identityModel */
            $identityModel = model(UserIdentityModel::class);

            $this->identities = $identityModel->getIdentities($this->id);
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
     */
    public function createEmailIdentity(array $credentials): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        /** @var Passwords $passwords */
        $passwords = service('passwords');

        $identityModel->insert([
            'user_id' => $this->id,
            'type'    => 'email_password',
            'secret'  => $credentials['email'],
            'secret2' => $passwords->hash($credentials['password']),
        ]);
    }

    /**
     * Returns the user's Email/Password identity.
     */
    public function getEmailIdentity(): ?UserIdentity
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        return $identityModel->getIdentityByType($this->id, 'email_password');
    }

    /**
     * Update the last used at date for an identity record.
     */
    public function touchIdentity(UserIdentity $identity): void
    {
        $identity->last_used_at = date('Y-m-d H:i:s');

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->save($identity);
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

    public function setPassword(string $password): void
    {
        $this->password = $password;
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
     * Returns the last login information for this
     * user as
     */
    public function lastLogin(bool $allowFailed = false): ?Login
    {
        $logins = model(LoginModel::class);

        if (! $allowFailed) {
            $logins->where('success', 1)
                ->where('user_id', $this->attributes['id'] ?? null);
        }

        return $logins
            ->where('email', $this->getAuthEmail())
            ->orderBy('date', 'desc')
            ->first();
    }

    /**
     * Returns the name of the column used to
     * uniquely identify this user, typically 'id'.
     */
    public function getAuthIdColumn(): string
    {
        return 'id';
    }

    /**
     * Returns the unique identifier of
     * the object for authentication purposes.
     * Typically the user's id.
     *
     * @return int|string
     */
    public function getAuthId()
    {
        $column = $this->getAuthIdColumn();
        $id     = $this->{$column} ?? null;

        assert($id !== null, 'User ID must not be null.');

        return $id;
    }

    /**
     * Returns the name of the column with the
     * email address of this user.
     */
    public function getAuthEmailColumn(): string
    {
        return 'email';
    }

    /**
     * Returns the email address for this user.
     */
    public function getAuthEmail(): ?string
    {
        $column = $this->getAuthEmailColumn();

        return $this->{$column} ?? null;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Returns the "remember me" token for this user.
     */
    public function getRememberToken(): ?string
    {
        $column = $this->getRememberColumn();

        return $this->{$column} ?? null;
    }

    /**
     * Sets the "remember-me" token.
     *
     * @return $this
     */
    public function setRememberToken(string $value): self
    {
        $column = $this->getRememberColumn();

        $this->{$column} = $value;

        return $this;
    }

    /**
     * Returns the column name that stores the remember-me value.
     */
    public function getRememberColumn(): string
    {
        return 'remember_token';
    }
}
