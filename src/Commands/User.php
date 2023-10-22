<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Commands;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Commands\Exceptions\BadInputException;
use CodeIgniter\Shield\Commands\Exceptions\CancelException;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User as UserEntity;
use CodeIgniter\Shield\Exceptions\UserNotFoundException;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Validation\ValidationRules;
use Config\Services;

class User extends BaseCommand
{
    private array $validActions = [
        'create', 'activate', 'deactivate', 'changename', 'changeemail',
        'delete', 'password', 'list', 'addgroup', 'removegroup',
    ];

    /**
     * Command's name
     *
     * @var string
     */
    protected $name = 'shield:user';

    /**
     * Command's short description
     *
     * @var string
     */
    protected $description = 'Manage Shield users.';

    /**
     * Command's usage
     *
     * @var string
     */
    protected $usage = <<<'EOL'
        shield:user <action> options

            shield:user create -n newusername -e newuser@example.com

            shield:user activate -n username
            shield:user activate -e user@example.com

            shield:user deactivate -n username
            shield:user deactivate -e user@example.com

            shield:user changename -n username --new-name newusername
            shield:user changename -e user@example.com --new-name newusername

            shield:user changeemail -n username --new-email newuseremail@example.com
            shield:user changeemail -e user@example.com --new-email newuseremail@example.com

            shield:user delete -i 123
            shield:user delete -n username
            shield:user delete -e user@example.com

            shield:user password -n username
            shield:user password -e user@example.com

            shield:user list
            shield:user list -n username -e user@example.com

            shield:user addgroup -n username -g mygroup
            shield:user addgroup -e user@example.com -g mygroup

            shield:user removegroup -n username -g mygroup
            shield:user removegroup -e user@example.com -g mygroup
        EOL;

    /**
     * Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'action' => <<<'EOL'

                create:      Create a new user
                activate:    Activate a user
                deactivate:  Deactivate a user
                changename:  Change user name
                changeemail: Change user email
                delete:      Delete a user
                password:    Change a user password
                list:        List users
                addgroup:    Add a user to a group
                removegroup: Remove a user from a group
            EOL,
    ];

    /**
     * Command's Options
     *
     * @var array
     */
    protected $options = [
        '-i'          => 'User id',
        '-n'          => 'User name',
        '-e'          => 'User email',
        '--new-name'  => 'New username',
        '--new-email' => 'New email',
        '-g'          => 'Group name',
    ];

    /**
     * Validation rules for user fields
     */
    private array $validationRules = [];

    /**
     * Auth Table names
     *
     * @var array<string, string>
     */
    private array $tables = [];

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params): int
    {
        $this->setTables();
        $this->setValidationRules();

        $action = $params[0] ?? null;

        if ($action === null || ! in_array($action, $this->validActions, true)) {
            $this->write(
                'Specify a valid action: ' . implode(',', $this->validActions),
                'red'
            );

            return EXIT_ERROR;
        }

        $userid      = (int) ($params['i'] ?? 0);
        $username    = $params['n'] ?? null;
        $email       = $params['e'] ?? null;
        $newUsername = $params['new-name'] ?? null;
        $newEmail    = $params['new-email'] ?? null;
        $group       = $params['g'] ?? null;

        try {
            switch ($action) {
                case 'create':
                    $this->create($username, $email);
                    break;

                case 'activate':
                    $this->activate($username, $email);
                    break;

                case 'deactivate':
                    $this->deactivate($username, $email);
                    break;

                case 'changename':
                    $this->changename($username, $email, $newUsername);
                    break;

                case 'changeemail':
                    $this->changeemail($username, $email, $newEmail);
                    break;

                case 'delete':
                    $this->delete($userid, $username, $email);
                    break;

                case 'password':
                    $this->password($username, $email);
                    break;

                case 'list':
                    $this->list($username, $email);
                    break;

                case 'addgroup':
                    $this->addgroup($group, $username, $email);
                    break;

                case 'removegroup':
                    $this->removegroup($group, $username, $email);
                    break;
            }
        } catch (BadInputException|CancelException|UserNotFoundException $e) {
            $this->write($e->getMessage(), 'red');

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }

    private function setTables(): void
    {
        /** @var Auth $config */
        $config       = config('Auth');
        $this->tables = $config->tables;
    }

    private function setValidationRules(): void
    {
        $validationRules = new ValidationRules();

        $rules = $validationRules->getRegistrationRules();

        // Remove `strong_password` because it only supports use cases
        // to check the user's own password.
        $passwordRules = $rules['password']['rules'];
        if (is_string($passwordRules)) {
            $passwordRules = explode('|', $passwordRules);
        }
        if (($key = array_search('strong_password[]', $passwordRules, true)) !== false) {
            unset($passwordRules[$key]);
        }
        if (($key = array_search('strong_password', $passwordRules, true)) !== false) {
            unset($passwordRules[$key]);
        }

        /** @var Auth $config */
        $config = config('Auth');

        // Add `min_length`
        $passwordRules[] = 'min_length[' . $config->minimumPasswordLength . ']';

        $rules['password']['rules'] = $passwordRules;

        $this->validationRules = [
            'username' => $rules['username'],
            'email'    => $rules['email'],
            'password' => $rules['password'],
        ];
    }

    /**
     * Create a new user
     *
     * @param string|null $username User name to create (optional)
     * @param string|null $email    User email to create (optional)
     */
    private function create(?string $username = null, ?string $email = null): void
    {
        $data = [];

        if ($username === null) {
            $username = $this->prompt('Username', null, $this->validationRules['username']['rules']);
        }
        $data['username'] = $username;

        if ($email === null) {
            $email = $this->prompt('Email', null, $this->validationRules['email']['rules']);
        }
        $data['email'] = $email;

        $password = $this->prompt(
            'Password',
            null,
            $this->validationRules['password']['rules']
        );
        $passwordConfirm = $this->prompt(
            'Password confirmation',
            null,
            $this->validationRules['password']['rules']
        );

        if ($password !== $passwordConfirm) {
            throw new BadInputException("The passwords don't match");
        }
        $data['password'] = $password;

        // Run validation if the user has passed username and/or email via command line
        $validation = Services::validation();
        $validation->setRules($this->validationRules);

        if (! $validation->run($data)) {
            foreach ($validation->getErrors() as $message) {
                $this->write($message, 'red');
            }

            throw new CancelException('User creation aborted');
        }

        $userModel = model(UserModel::class);

        $user = new UserEntity($data);
        $userModel->save($user);

        $this->write('User "' . $username . '" created', 'green');
    }

    /**
     * Activate an existing user by username or email
     *
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function activate(?string $username = null, ?string $email = null): void
    {
        $user = $this->findUser('Activate user', $username, $email);

        $confirm = $this->prompt('Activate the user ' . $user->username . ' ?', ['y', 'n']);

        if ($confirm === 'y') {
            $userModel = model(UserModel::class);

            $user->active = 1;
            $userModel->save($user);

            $this->write('User "' . $user->username . '" activated', 'green');
        } else {
            $this->write('User "' . $user->username . '" activation cancelled', 'yellow');
        }
    }

    /**
     * Deactivate an existing user by username or email
     *
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function deactivate(?string $username = null, ?string $email = null): void
    {
        $user = $this->findUser('Deactivate user', $username, $email);

        $confirm = $this->prompt('Deactivate the user "' . $username . '" ?', ['y', 'n']);

        if ($confirm === 'y') {
            $userModel = model(UserModel::class);

            $user->active = 0;
            $userModel->save($user);

            $this->write('User "' . $user->username . '" deactivated', 'green');
        } else {
            $this->write('User "' . $user->username . '" deactivation cancelled', 'yellow');
        }
    }

    /**
     * Change the name of an existing user by username or email
     *
     * @param string|null $username    User name to search for (optional)
     * @param string|null $email       User email to search for (optional)
     * @param string|null $newUsername User new name (optional)
     */
    private function changename(
        ?string $username = null,
        ?string $email = null,
        ?string $newUsername = null
    ): void {
        $user = $this->findUser('Change username', $username, $email);

        if ($newUsername === null) {
            $newUsername = $this->prompt('New username', null, $this->validationRules['username']['rules']);
        } else {
            // Run validation if the user has passed username and/or email via command line
            $validation = Services::validation();
            $validation->setRules([
                'username' => $this->validationRules['username'],
            ]);

            if (! $validation->run(['username' => $newUsername])) {
                foreach ($validation->getErrors() as $message) {
                    $this->write($message, 'red');
                }

                throw new CancelException('User name change aborted');
            }
        }

        $userModel = model(UserModel::class);

        $oldUsername    = $user->username;
        $user->username = $newUsername;
        $userModel->save($user);

        $this->write('Username "' . $oldUsername . '" changed to "' . $newUsername . '"', 'green');
    }

    /**
     * Change the email of an existing user by username or email
     *
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     * @param string|null $newEmail User new email (optional)
     */
    private function changeemail(
        ?string $username = null,
        ?string $email = null,
        ?string $newEmail = null
    ): void {
        $user = $this->findUser('Change email', $username, $email);

        if ($newEmail === null) {
            $newEmail = $this->prompt('New email', null, $this->validationRules['email']['rules']);
        } else {
            // Run validation if the user has passed username and/or email via command line
            $validation = Services::validation();
            $validation->setRules([
                'email' => $this->validationRules['email'],
            ]);

            if (! $validation->run(['email' => $newEmail])) {
                foreach ($validation->getErrors() as $message) {
                    $this->write($message, 'red');
                }

                throw new CancelException('User email change aborted');
            }
        }

        $userModel = model(UserModel::class);

        $user->email = $newEmail;
        $userModel->save($user);

        $this->write('Email for "' . $user->username . '" changed to ' . $newEmail, 'green');
    }

    /**
     * Delete an existing user by username or email
     *
     * @param int         $userid   User id to delete (optional)
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function delete(int $userid = 0, ?string $username = null, ?string $email = null): void
    {
        $userModel = model(UserModel::class);

        if ($userid !== 0) {
            $user = $userModel->findById($userid);

            $this->checkUserExists($user);
        } else {
            $user = $this->findUser('Delete user', $username, $email);
        }

        $confirm = $this->prompt(
            'Delete the user "' . $user->username . '" (' . $user->email . ') ?',
            ['y', 'n']
        );

        if ($confirm === 'y') {
            $userModel->delete($user->id, true);

            $this->write('User "' . $user->username . '" deleted', 'green');
        } else {
            $this->write('User "' . $user->username . '" deletion cancelled', 'yellow');
        }
    }

    /**
     * @param UserEntity|null $user
     */
    private function checkUserExists($user): void
    {
        if ($user === null) {
            throw new UserNotFoundException("User doesn't exist");
        }
    }

    /**
     * Change the password of an existing user by username or email
     *
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function password($username = null, $email = null): void
    {
        $user = $this->findUser('Change user password', $username, $email);

        $confirm = $this->prompt('Set the password for "' . $user->username . '" ?', ['y', 'n']);

        if ($confirm === 'y') {
            $password = $this->prompt(
                'Password',
                null,
                $this->validationRules['password']['rules']
            );
            $passwordConfirm = $this->prompt(
                'Password confirmation',
                null,
                $this->validationRules['password']['rules']
            );

            if ($password !== $passwordConfirm) {
                throw new BadInputException("The passwords don't match");
            }

            $userModel = model(UserModel::class);

            $user->password = $password;
            $userModel->save($user);

            $this->write('Password for "' . $user->username . '" set', 'green');
        } else {
            $this->write('Password setting for "' . $user->username . '" cancelled', 'yellow');
        }
    }

    /**
     * List users searching by username or email
     *
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function list(?string $username = null, ?string $email = null): void
    {
        $userModel = model(UserModel::class);
        $userModel
            ->select($this->tables['users'] . '.id as id, username, secret as email')
            ->join(
                $this->tables['identities'],
                $this->tables['users'] . '.id = ' . $this->tables['identities'] . '.user_id',
                'LEFT'
            )
            ->groupStart()
            ->where($this->tables['identities'] . '.type', Session::ID_TYPE_EMAIL_PASSWORD)
            ->orGroupStart()
            ->where($this->tables['identities'] . '.type', null)
            ->groupEnd()
            ->groupEnd()
            ->asArray();

        if ($username !== null) {
            $userModel->like('username', $username);
        }
        if ($email !== null) {
            $userModel->like('secret', $email);
        }

        $this->write("Id\tUser");

        foreach ($userModel->findAll() as $user) {
            $this->write($user['id'] . "\t" . $user['username'] . ' (' . $user['email'] . ')');
        }
    }

    /**
     * Add a user by username or email to a group
     *
     * @param string|null $group    Group to add user to
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function addgroup($group = null, $username = null, $email = null): void
    {
        if ($group === null) {
            $group = $this->prompt('Group', null, 'required');
        }

        $user = $this->findUser('Add user to group', $username, $email);

        $confirm = $this->prompt(
            'Add the user "' . $user->username . '" to the group "' . $group . '" ?',
            ['y', 'n']
        );

        if ($confirm === 'y') {
            $user->addGroup($group);

            $this->write('User "' . $user->username . '" added to group "' . $group . '"', 'green');
        } else {
            $this->write(
                'Addition of the user "' . $user->username . '" to the group "' . $group . '" cancelled',
                'yellow'
            );
        }
    }

    /**
     * Remove a user by username or email from a group
     *
     * @param string|null $group    Group to remove user from
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function removegroup($group = null, $username = null, $email = null): void
    {
        if ($group === null) {
            $group = $this->prompt('Group', null, 'required');
        }

        $user = $this->findUser('Remove user from group', $username, $email);

        $confirm = $this->prompt(
            'Remove the user "' . $user->username . '" from the group "' . $group . '" ?',
            ['y', 'n']
        );

        if ($confirm === 'y') {
            $user->removeGroup($group);

            $this->write('User "' . $user->username . '" removed from group "' . $group . '"', 'green');
        } else {
            $this->write('Removal of the user "' . $user->username . '" from the group "' . $group . '" cancelled', 'yellow');
        }
    }

    /**
     * Find an existing user by username or email.
     *
     * @param string      $question Initial question at user prompt
     * @param string|null $username User name to search for (optional)
     * @param string|null $email    User email to search for (optional)
     */
    private function findUser($question = '', $username = null, $email = null): UserEntity
    {
        if ($username === null && $email === null) {
            $choice = $this->prompt($question . ' by username or email ?', ['u', 'e']);

            if ($choice === 'u') {
                $username = $this->prompt('Username', null, 'required');
            } elseif ($choice === 'e') {
                $email = $this->prompt(
                    'Email',
                    null,
                    'required'
                );
            }
        }

        $userModel = model(UserModel::class);
        $userModel
            ->select($this->tables['users'] . '.id as id, username, secret')
            ->join(
                $this->tables['identities'],
                $this->tables['users'] . '.id = ' . $this->tables['identities'] . '.user_id',
                'LEFT'
            )
            ->groupStart()
            ->where($this->tables['identities'] . '.type', Session::ID_TYPE_EMAIL_PASSWORD)
            ->orGroupStart()
            ->where($this->tables['identities'] . '.type', null)
            ->groupEnd()
            ->groupEnd()
            ->asArray();

        $user = null;
        if ($username !== null) {
            $user = $userModel->where('username', $username)->first();
        } elseif ($email !== null) {
            $user = $userModel->where('secret', $email)->first();
        }

        $this->checkUserExists($user);

        return $userModel->findById($user['id']);
    }
}
