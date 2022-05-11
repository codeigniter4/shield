<?php

namespace CodeIgniter\Shield\Authentication\Traits;

/**
 * Intended to be used with User.
 */
trait Authenticatable
{
    /**
     * Returns the unique identifier of
     * the object for authentication purposes.
     * Typically the user's id.
     *
     * @return int|string
     */
    public function getAuthId()
    {
        $id = $this->id;

        assert($id !== null, 'User ID must not be null.');

        return $id;
    }

    /**
     * Returns the email address for this user.
     */
    public function getAuthEmail(): ?string
    {
        return $this->getEmail();
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
