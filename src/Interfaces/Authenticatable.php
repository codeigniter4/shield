<?php

namespace CodeIgniter\Shield\Interfaces;

interface Authenticatable
{
    /**
     * Returns the name of the column used to
     * uniquely identify this user, typically 'id'.
     */
    public function getAuthIdColumn(): string;

    /**
     * Returns the unique identifier of
     * the User object for authentication purposes.
     * Typically, the user's id.
     *
     * @return int|string
     */
    public function getAuthId();

    /**
     * Returns the name of the column with the
     * email address of this user.
     */
    public function getAuthEmailColumn(): string;

    /**
     * Returns the email address for this user.
     */
    public function getAuthEmail(): ?string;

    /**
     * Get the password for the user.
     */
    public function getAuthPassword(): ?string;

    /**
     * Returns the "remember-me" token for this user.
     */
    public function getRememberToken(): ?string;

    /**
     * Sets the "remember-me" token.
     *
     * @return $this
     */
    public function setRememberToken(string $value);

    /**
     * Returns the column name that stores the remember-me value.
     */
    public function getRememberColumn(): string;
}
