<?php

namespace CodeIgniter\Shield\Authentication\Traits;

/**
 * Intended to be used with Authenticatable (User).
 */
trait Authenticatable
{
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
    public function setRememberToken(string $value)
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
