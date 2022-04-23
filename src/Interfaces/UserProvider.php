<?php

namespace Sparks\Shield\Interfaces;

interface UserProvider
{
    /**
     * Locates a User object by ID.
     *
     * @return Authenticatable|null
     */
    public function findById(int $id);

    /**
     * Locate a user by the given credentials.
     *
     * @return Authenticatable|null
     */
    public function findByCredentials(array $credentials);

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @return Authenticatable|null
     */
    public function findByRememberToken(int $id, string $token);

    /**
     * Given a User object, will update the record
     * in the database. This is often a User entity
     * that already has the correct values set.
     *
     * @param array|object $data
     */
    public function save($data);
}
