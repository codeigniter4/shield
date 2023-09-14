<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class User extends BaseCommand
{
    private array $valid_actions = ['create', 'activate', 'deactivate', 'changename', 'changeemail', 'delete', 'password', 'list', 'addgroup', 'removegroup'];

    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Shield';

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
    protected $description = 'Manage Shield users';

    /**
     * Command's usage
     *
     * @var string
     */
    protected $usage = 'shield:user <action>';

    /**
     * Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'action' => 'Valid actions : create, activate, deactivate, changename, changeemail, delete, password, list, addgroup, removegroup',
    ];

    /**
     * Command's Options
     *
     * @var array
     */
    protected $options = [
        '-i' => 'User id',
        '-u' => 'User name',
        '-e' => 'User email',
        '-n' => 'New username',
        '-m' => 'New email',
        '-g' => 'Group name',
    ];

    /**
     * Validation rules for user fields
     */
    private array $validation_rules = [
        'username' => 'required|is_unique[users.username]',
        'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
        'password' => 'required|min_length[10]',
    ];

    /**
     * Displays the help for the spark cli script itself.
     */
    public function run(array $params): void
    {
        $action = CLI::getSegment(2);
        if ($action && in_array($action, $this->valid_actions, true)) {
            $userid       = (int) CLI::getOption('i');
            $username     = CLI::getOption('u');
            $email        = CLI::getOption('e');
            $new_username = CLI::getOption('n');
            $new_email    = CLI::getOption('m');
            $group        = CLI::getOption('g');

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
                    $this->changename($username, $email, $new_username);
                    break;

                case 'changeemail':
                    $this->changeemail($username, $email, $new_email);
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
        } else {
            CLI::write('Specify a valid action : ' . implode(',', $this->valid_actions), 'red');
        }
    }

    /**
     * Create a new user
     *
     * @param $username User name to create (optional)
     * @param $email    User email to create (optional)
     */
    private function create(?string $username = null, ?string $email = null): void
    {
        $data = [];

        if (! $username) {
            $username = CLI::prompt('Username', null, $this->validation_rules['username']);
        }
        $data['username'] = $username;

        if (! $email) {
            $email = CLI::prompt('Email', null, $this->validation_rules['email']);
        }
        $data['email'] = $email;

        $password         = CLI::prompt('Password', null, $this->validation_rules['password']);
        $password_confirm = CLI::prompt('Password confirmation', null, $this->validation_rules['password']);

        if ($password !== $password_confirm) {
            CLI::write("The passwords don't match", 'red');

            exit;
        }
        $data['password'] = $password;

        // Run validation if the user has passed username and/or email via command line
        $validation = Services::validation();
        $validation->setRules($this->validation_rules);
        if (! $validation->run($data)) {
            foreach ($validation->getErrors() as $message) {
                CLI::write($message, 'red');
            }
            CLI::write('User creation aborted', 'red');

            exit;
        }

        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $user      = new \CodeIgniter\Shield\Entities\User($data);
        $userModel->save($user);
        CLI::write('User ' . $username . ' created', 'green');
    }

    /**
     * Activate an existing user by username or email
     *
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function activate(?string $username = null, ?string $email = null): void
    {
        $user    = $this->findUser('Activate user', $username, $email);
        $confirm = CLI::prompt('Activate the user ' . $user->username . ' ?', ['y', 'n']);
        if ($confirm === 'y') {
            $userModel    = model('CodeIgniter\Shield\Models\UserModel');
            $user->active = 1;
            $userModel->save($user);
            CLI::write('User ' . $user->username . ' activated', 'green');
        } else {
            CLI::write('User ' . $user->username . ' activation cancelled', 'yellow');
        }
    }

    /**
     * Deactivate an existing user by username or email
     *
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function deactivate(?string $username = null, ?string $email = null): void
    {
        $user    = $this->findUser('Deactivate user', $username, $email);
        $confirm = CLI::prompt('Deactivate the user ' . $username . ' ?', ['y', 'n']);
        if ($confirm === 'y') {
            $userModel    = model('CodeIgniter\Shield\Models\UserModel');
            $user->active = 0;
            $userModel->save($user);
            CLI::write('User ' . $user->username . ' deactivated', 'green');
        } else {
            CLI::write('User ' . $user->username . ' deactivation cancelled', 'yellow');
        }
    }

    /**
     * Change the name of an existing user by username or email
     *
     * @param $username     User name to search for (optional)
     * @param $email        User email to search for (optional)
     * @param $new_username User new name (optional)
     */
    private function changename(?string $username = null, ?string $email = null, ?string $new_username = null): void
    {
        $validation = Services::validation();
        $validation->setRules([
            'username' => 'required|is_unique[users.username]',
        ]);

        $user = $this->findUser('Change username', $username, $email);

        if (! $new_username) {
            $new_username = CLI::prompt('New username', null, $this->validation_rules['username']);
        } else {
            // Run validation if the user has passed username and/or email via command line
            $validation = Services::validation();
            $validation->setRules([
                'username' => $this->validation_rules['username'],
            ]);
            if (! $validation->run(['username' => $new_username])) {
                foreach ($validation->getErrors() as $message) {
                    CLI::write($message, 'red');
                }
                CLI::write('User name change aborted', 'red');

                exit;
            }
        }

        $userModel      = model('CodeIgniter\Shield\Models\UserModel');
        $old_username   = $user->username;
        $user->username = $new_username;
        $userModel->save($user);
        CLI::write('Username ' . $old_username . ' changed to ' . $new_username, 'green');
    }

    /**
     * Change the email of an existing user by username or email
     *
     * @param $username  User name to search for (optional)
     * @param $email     User email to search for (optional)
     * @param $new_email User new email (optional)
     */
    private function changeemail(?string $username = null, ?string $email = null, ?string $new_email = null): void
    {
        $user = $this->findUser('Change email', $username, $email);

        if (! $new_email) {
            $new_email = CLI::prompt('New email', null, $this->validation_rules['email']);
        } else {
            // Run validation if the user has passed username and/or email via command line
            $validation = Services::validation();
            $validation->setRules([
                'email' => $this->validation_rules['email'],
            ]);
            if (! $validation->run(['email' => $new_email])) {
                foreach ($validation->getErrors() as $message) {
                    CLI::write($message, 'red');
                }
                CLI::write('User email change aborted', 'red');

                exit;
            }
        }

        $userModel   = model('CodeIgniter\Shield\Models\UserModel');
        $user->email = $new_email;
        $userModel->save($user);
        CLI::write('Email for the user : ' . $user->username . ' changed to ' . $new_email, 'green');
    }

    /**
     * Delete an existing user by username or email
     *
     * @param $userid   User id to delete (optional)
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function delete(int $userid = 0, ?string $username = null, ?string $email = null): void
    {
        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        if ($userid) {
            $user = $userModel->findById($userid);
            if (! $user) {
                CLI::write("User doesn't exist", 'red');

                exit;
            }
        } else {
            $user = $this->findUser('Delete user', $username, $email);
        }

        $confirm = CLI::prompt('Delete the user ' . $user->username . ' (' . $user->email . ') ?', ['y', 'n']);
        if ($confirm === 'y') {
            $userModel->delete($user->id, true);
            CLI::write('User ' . $user->username . ' deleted', 'green');
        } else {
            CLI::write('User ' . $user->username . ' deletion cancelled', 'yellow');
        }
    }

    /**
     * Change the password of an existing user by username or email
     *
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function password($username = null, $email = null): void
    {
        $user = $this->findUser('Change user password', $username, $email);

        $confirm = CLI::prompt('Set the password for the user ' . $user->username . ' ?', ['y', 'n']);
        if ($confirm === 'y') {
            $password         = CLI::prompt('Password', null, 'required');
            $password_confirm = CLI::prompt('Password confirmation', null, $this->validation_rules['password']);

            if ($password !== $password_confirm) {
                CLI::write("The passwords don't match", 'red');

                exit;
            }

            $userModel      = model('CodeIgniter\Shield\Models\UserModel');
            $user->password = $password;
            $userModel->save($user);

            CLI::write('Password for the user ' . $user->username . ' set', 'green');
        } else {
            CLI::write('Password setting for the user : ' . $user->username . ', cancelled', 'yellow');
        }
    }

    /**
     * List users searching by username or email
     *
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function list(?string $username = null, ?string $email = null): void
    {
        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $users     = $userModel->join('auth_identities', 'auth_identities.user_id = users.id');
        if ($username) {
            $users = $users->like('username', $username);
        }
        if ($email) {
            $users = $users->like('secret', $email);
        }

        CLI::write("Id\tUser");

        foreach ($users->findAll() as $user) {
            CLI::write($user->id . "\t" . $user->username . ' (' . $user->secret . ')');
        }
    }

    /**
     * Add a user by username or email to a group
     *
     * @param $group    Group to add user to
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function addgroup($group = null, $username = null, $email = null): void
    {
        if (! $group) {
            $group = CLI::prompt('Group', null, 'required');
        }

        $user = $this->findUser('Add user to group', $username, $email);

        $confirm = CLI::prompt('Add the user: ' . $user->username . ' to the group: ' . $group . ' ?', ['y', 'n']);
        if ($confirm === 'y') {
            $user->addGroup($group);
            CLI::write('User ' . $user->username . ' added to group ' . $group, 'green');
        } else {
            CLI::write('Addition of the user: ' . $user->username . ' to the group: ' . $group . ' cancelled', 'yellow');
        }
    }

    /**
     * Remove a user by username or email from a group
     *
     * @param $group    Group to remove user from
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function removegroup($group = null, $username = null, $email = null): void
    {
        if (! $group) {
            $group = CLI::prompt('Group', null, 'required');
        }

        $user = $this->findUser('Remove user from group', $username, $email);

        $confirm = CLI::prompt('Remove the user: ' . $user->username . ' fromt the group: ' . $group . ' ?', ['y', 'n']);
        if ($confirm === 'y') {
            $user->removeGroup($group);
            CLI::write('User ' . $user->username . ' removed from group ' . $group, 'green');
        } else {
            CLI::write('Removal of the user: ' . $user->username . ' from the group: ' . $group . ' cancelled', 'yellow');
        }
    }

    /**
     * Find an existing user by username or email. Exit if a user is not found
     *
     * @param $question Initial question at user prompt
     * @param $username User name to search for (optional)
     * @param $email    User email to search for (optional)
     */
    private function findUser($question = '', $username = null, $email = null): \CodeIgniter\Shield\Entities\User
    {
        if (! $username && ! $email) {
            $choice = CLI::prompt($question . ' by username or email ?', ['u', 'e']);
            if ($choice === 'u') {
                $username = CLI::prompt('Username', null, 'required');
            } elseif ($choice === 'e') {
                $email = CLI::prompt('Email', null, 'required|valid_email');
            }
        }

        $user      = new \CodeIgniter\Shield\Entities\User();
        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $users     = $userModel->join('auth_identities', 'auth_identities.user_id = users.id');

        if ($username) {
            $user = $users->where('username', $username)->first();
        } elseif ($email) {
            $user = $users->where('secret', $email)->first();
        }

        if (! $user) {
            CLI::write("User doesn't exist", 'red');

            exit;
        }

        return $user;
    }
}
