<?php

namespace Sparks\Shield\Entities;

use CodeIgniter\Entity\Entity;
use Sparks\Shield\Authentication\Traits\Authenticatable;
use Sparks\Shield\Authentication\Traits\HasAccessTokens;
use Sparks\Shield\Authorization\Traits\Authorizable;
use Sparks\Shield\Models\UserIdentityModel;

class User extends Entity implements \Sparks\Shield\Interfaces\Authenticatable
{
    use Authenticatable;
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

    protected $casts = [
        'active'           => 'boolean',
        'force_pass_reset' => 'boolean',
        'permissions'      => 'array',
        'groups'           => 'array',
    ];

    /**
     * Returns the first identity of the given $type for this user.
     *
     * @return mixed|null
     */
    public function getIdentity(string $type)
    {
        $identities = $this->identitiesOfType($type);

        return count($identities) ? array_shift($identities) : null;
    }

    /**
     * Accessor method for this user's UserIdentity objects.
     * Will populate if they don't exist.
     */
    public function getIdentities(): array
    {
        if ($this->identities === null) {
            /** @var UserIdentityModel $identityModel */
            $identityModel = model(UserIdentityModel::class);

            $this->identities = $identityModel->getIdentities($this->id);
        }

        return $this->identities;
    }

    /**
     * Returns only the user identities that match the given $type.
     */
    public function identitiesOfType(string $type): array
    {
        $identities = [];

        foreach ($this->getIdentities() as $identity) {
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
    public function createEmailIdentity(array $credentials)
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identityModel->insert([
            'user_id' => $this->id,
            'type'    => 'email_password',
            'secret'  => $credentials['email'],
            'secret2' => service('passwords')->hash($credentials['password']),
        ]);
    }

    /**
     * Returns the user's Email/Password identity.
     *
     * @return mixed
     */
    public function getEmailIdentity()
    {
        return $this->getIdentity('email_password');
    }

    /**
     * Update the last used at date for an identity record.
     */
    public function touchIdentity(UserIdentity $identity)
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
    public function getEmail()
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
    public function getPasswordHash()
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
    public function lastLogin(bool $allowFailed = false)
    {
        $logins = model('LoginModel'); // @phpstan-ignore-line

        if (! $allowFailed) {
            $logins->where('success', 1)
                ->where('user_id', $this->attributes['id'] ?? null);
        }

        return $logins
            ->where('identifier', $this->getAuthEmail())
            ->orderBy('date', 'desc')
            ->first();
    }
}
