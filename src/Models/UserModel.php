<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Model;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException;
use Faker\Generator;

class UserModel extends Model
{
    use CheckQueryReturnTrait;

    protected $table          = 'users';
    protected $primaryKey     = 'id';
    protected $returnType     = User::class;
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'username',
        'status',
        'status_message',
        'active',
        'last_active',
        'deleted_at',
        'permissions',
    ];
    protected $useTimestamps = true;
    protected $afterFind     = ['fetchIdentities'];

    /**
     * Whether identity records should be included
     * when user records are fetched from the database.
     */
    protected bool $fetchIdentities = false;

    /**
     * Mark the next find* query to include identities
     *
     * @return $this
     */
    public function withIdentities(): self
    {
        $this->fetchIdentities = true;

        return $this;
    }

    /**
     * Populates identities for all records
     * returned from a find* method. Called
     * automatically when $this->fetchIdentities == true
     *
     * Model event callback called `afterFind`.
     */
    protected function fetchIdentities(array $data): array
    {
        if (! $this->fetchIdentities) {
            return $data;
        }

        $userIds = $data['singleton']
            ? array_column($data, 'id')
            : array_column($data['data'], 'id');

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // Get our identities for all users
        $identities = $identityModel->getIdentitiesByUserIds($userIds);

        if (empty($identities)) {
            return $data;
        }

        // Map our users by ID to make assigning simpler
        $mappedUsers = [];
        $users       = $data['singleton']
            ? $data
            : $data['data'];

        foreach ($users as $user) {
            $mappedUsers[$user->id] = $user;
        }
        unset($users);

        // Now assign the identities to the user
        foreach ($identities as $id) {
            $array                                 = $mappedUsers[$id->user_id]->identities;
            $array[]                               = $id;
            $mappedUsers[$id->user_id]->identities = $array;
        }

        $data['data'] = $mappedUsers;

        return $data;
    }

    /**
     * Adds a user to the default group.
     * Used during registration.
     */
    public function addToDefaultGroup(User $user): void
    {
        $defaultGroup  = setting('AuthGroups.defaultGroup');
        $allowedGroups = array_keys(setting('AuthGroups.groups'));

        if (empty($defaultGroup) || ! in_array($defaultGroup, $allowedGroups, true)) {
            throw new InvalidArgumentException(lang('Auth.unknownGroup', [$defaultGroup ?? '--not found--']));
        }

        $user->addGroup($defaultGroup);
    }

    public function fake(Generator &$faker): User
    {
        return new User([
            'username' => $faker->userName,
            'active'   => true,
        ]);
    }

    /**
     * Locates a User object by ID.
     *
     * @param int|string $id
     */
    public function findById($id): ?User
    {
        return $this->find($id);
    }

    /**
     * Locate a User object by the given credentials.
     *
     * @param array<string, string> $credentials
     */
    public function findByCredentials(array $credentials): ?User
    {
        // Email is stored in an identity so remove that here
        $email = $credentials['email'] ?? null;
        unset($credentials['email']);

        $prefix = $this->db->DBPrefix;

        // any of the credentials used should be case-insensitive
        foreach ($credentials as $key => $value) {
            $this->where("LOWER({$prefix}users.{$key})", strtolower($value));
        }

        if (! empty($email)) {
            $data = $this->select('users.*, auth_identities.secret as email, auth_identities.secret2 as password_hash')
                ->join('auth_identities', 'auth_identities.user_id = users.id')
                ->where('auth_identities.type', Session::ID_TYPE_EMAIL_PASSWORD)
                ->where("LOWER({$prefix}auth_identities.secret)", strtolower($email))
                ->asArray()
                ->first();

            if ($data === null) {
                return null;
            }

            $email = $data['email'];
            unset($data['email']);
            $password_hash = $data['password_hash'];
            unset($data['password_hash']);

            $user                = new User($data);
            $user->email         = $email;
            $user->password_hash = $password_hash;
            $user->syncOriginal();

            return $user;
        }

        return $this->first();
    }

    /**
     * Activate a User.
     */
    public function activate(User $user): void
    {
        $user->active = true;

        $this->saveWithEmailIdentity($user);
    }

    /**
     * Save User and its Email Identity (email, password, or password_hash fields)
     * if they've been modified.
     */
    public function saveWithEmailIdentity(User $data): void
    {
        try {
            /** @throws DataException */
            $result = parent::save($data);
        } catch (DataException $e) {
            $messages = [
                lang('Database.emptyDataset', ['insert']),
                lang('Database.emptyDataset', ['update']),
            ];
            if (in_array($e->getMessage(), $messages, true)) {
                // Save updated email identity
                $user = $data;
                $user->saveEmailIdentity();

                return;
            }

            throw $e;
        }

        if ($result) {
            if ($data->id === null) {
                // Insert
                /** @var User $user */
                $user = $this->find($this->db->insertID());

                $user->email         = $data->email ?? null;
                $user->password      = $data->password ?? '';
                $user->password_hash = $data->password_hash ?? '';
            } else {
                // Update
                $user = $data;
            }

            $user->saveEmailIdentity();
        }

        $this->checkQueryReturn($result);
    }
}
