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
     * Accessor method for this user's UserIdentity records.
     * Will populate if they don't exist.
     */
    public function getIdentities(): array
    {
        if (! array_key_exists('identities', $this->attributes) || ! is_array($this->attributes['identities'])) {
            $this->attributes['identities'] = model(UserIdentityModel::class)
                ->where('user_id', $this->id)
                ->findAll();
        }

        return $this->attributes['identities'];
    }

    /**
     * Returns only the user identities that match the given $type.
     */
    public function identitiesOfType(string $type): array
    {
        $identities = [];

        foreach ($this->identities as $id) {
            if ($id->type === $type) {
                $identities[] = $id;
            }
        }

        return $identities;
    }

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
     * Creates a new identity for this user with an email/password
     * combination.
     */
    public function createEmailIdentity(array $credentials)
    {
        model(UserIdentityModel::class)->insert([
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
        return model(UserIdentityModel::class)
            ->where('user_id', $this->id)
            ->where('type', 'email_password')
            ->first();
    }

    /**
     * Update the last used at date for an identity record.
     *
     * @param \Sparks\Shield\Entities\UserIdentity $identity
     */
    public function touchIdentity(UserIdentity $identity)
    {
        $identity->last_used_at = date('Y-m-d H:i:s');

        model(UserIdentityModel::class)->save($identity);
    }

    /**
     * Accessor method to grab the user's email address.
     * Will cache it in $this->attributes, since it has
     * to hit the database the first time to get it, most likely.
     */
    public function getEmail()
    {
        if (! isset($this->attributes['email'])) {
            $this->attributes['email'] = $this->getEmailIdentity()->secret ?? null;
        }

        return $this->attributes['email'];
    }

    /**
     * Accessor method to grab the user's email address.
     * Will cache it in $this->attributes, since it has
     * to hit the database the first time to get it, most likely.
     */
    public function getPasswordHash()
    {
        if (! isset($this->attributes['password_hash'])) {
            $this->attributes['password_hash'] = $this->getEmailIdentity()->secret2 ?? null;
        }

        return $this->attributes['password_hash'];
    }

    /**
     * Returns the last login information for this
     * user as
     */
    public function lastLogin(bool $allowFailed = false)
    {
        $logins = model('LoginModel');

        if (! $allowFailed) {
            $logins->where('success', 1)
                ->where('user_id', $this->attributes['id'] ?? null);
        }

        return $logins
            ->where('email', $this->getAuthEmail())
            ->orderBy('date', 'desc')
            ->first();
    }
}
