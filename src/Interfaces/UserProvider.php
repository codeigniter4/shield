<?php

namespace CodeIgniter\Shield\Interfaces;

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
     * @param array<string, string> $credentials
     */
    public function findByCredentials(array $credentials): ?Authenticatable;

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
     */
    public function save(Authenticatable $data): bool;
}
