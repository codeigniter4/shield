<?php

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Model;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException;
use CodeIgniter\Shield\Exceptions\ValidationException;
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
    ];
    protected $useTimestamps = true;
    protected $afterFind     = ['fetchIdentities'];
    protected $afterInsert   = ['saveEmailIdentity'];
    protected $afterUpdate   = ['saveEmailIdentity'];

    /**
     * Whether identity records should be included
     * when user records are fetched from the database.
     */
    protected bool $fetchIdentities = false;

    /**
     * Save the User for afterInsert and afterUpdate
     */
    protected ?User $tempUser = null;

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
     * Model event callback called by `afterFind`.
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

        $mappedUsers = $this->assignIdentities($data, $identities);

        $data['data'] = $data['singleton'] ? $mappedUsers[$data['id']] : $mappedUsers;

        return $data;
    }

    /**
     * Map our users by ID to make assigning simpler
     *
     * @param array          $data       Event $data
     * @param UserIdentity[] $identities
     *
     * @return User[] UserId => User object
     * @phpstan-return array<int|string, User> UserId => User object
     */
    private function assignIdentities(array $data, array $identities): array
    {
        $mappedUsers = [];

        $users = $data['singleton'] ? [$data['data']] : $data['data'];

        foreach ($users as $user) {
            $mappedUsers[$user->id] = $user;
        }
        unset($users);

        // Now assign the identities to the user
        foreach ($identities as $identity) {
            $userId = $identity->user_id;

            $newIdentities   = $mappedUsers[$userId]->identities;
            $newIdentities[] = $identity;

            $mappedUsers[$userId]->identities = $newIdentities;
        }

        return $mappedUsers;
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

        $this->save($user);
    }

    /**
     * Override the BaseModel's `insert()` method.
     * If you pass User object, also inserts Email Identity.
     *
     * @param array|User $data
     *
     * @throws ValidationException
     *
     * @retrun true|int|string Insert ID if $returnID is true
     */
    public function insert($data = null, bool $returnID = true)
    {
        $this->tempUser = $data instanceof User ? $data : null;

        $result = parent::insert($data, $returnID);

        $this->checkQueryReturn($result);

        return $returnID ? $this->insertID : $result;
    }

    /**
     * Override the BaseModel's `update()` method.
     * If you pass User object, also updates Email Identity.
     *
     * @param array|int|string|null $id
     * @param array|User            $data
     *
     * @throws ValidationException
     */
    public function update($id = null, $data = null): bool
    {
        $this->tempUser = $data instanceof User ? $data : null;

        try {
            /** @throws DataException */
            $result = parent::update($id, $data);
        } catch (DataException $e) {
            $messages = [
                lang('Database.emptyDataset', ['update']),
            ];

            if (in_array($e->getMessage(), $messages, true)) {
                $this->tempUser->saveEmailIdentity();

                return true;
            }

            throw $e;
        }

        $this->checkQueryReturn($result);

        return true;
    }

    /**
     * Override the BaseModel's `save()` method.
     * If you pass User object, also updates Email Identity.
     *
     * @param array|User $data
     *
     * @throws ValidationException
     */
    public function save($data): bool
    {
        $result = parent::save($data);

        $this->checkQueryReturn($result);

        return true;
    }

    /**
     * Save Email Identity
     *
     * Model event callback called by `afterInsert` and `afterUpdate`.
     */
    protected function saveEmailIdentity(array $data): array
    {
        // If insert()/update() gets an array data, do nothing.
        if ($this->tempUser === null) {
            return $data;
        }

        // Insert
        if ($this->tempUser->id === null) {
            /** @var User $user */
            $user = $this->find($this->db->insertID());

            $user->email         = $this->tempUser->email ?? '';
            $user->password      = $this->tempUser->password ?? '';
            $user->password_hash = $this->tempUser->password_hash ?? '';

            $user->saveEmailIdentity();
            $this->tempUser = null;

            return $data;
        }

        // Update
        $this->tempUser->saveEmailIdentity();
        $this->tempUser = null;

        return $data;
    }
}
