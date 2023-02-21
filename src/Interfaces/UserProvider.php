<?php

namespace Sparks\Shield\Interfaces;

interface UserProvider
{
    /**
     * Locates a User object by ID.
     *
     * @param int|string $id
     */
    public function findById($id): ?Authenticatable;

    /**
     * Locate a user by the given credentials.
     *
     * @return Authenticatable|null
     */
    public function findByCredentials(array $credentials);

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @param int|string $id
     */
    public function findByRememberToken($id, string $token): ?Authenticatable;

    /**
     * Given a User object, will update the record
     * in the database. This is often a User entity
     * that already has the correct values set.
     *
     * @param array|object $data
     */
    public function save($data);
}
