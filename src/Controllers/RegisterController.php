<?php

namespace CodeIgniter\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Interfaces\UserProvider;
use CodeIgniter\Validation\Validation;

/**
 * Class RegisterController
 *
 * Handles displaying registration form,
 * and handling actual registration flow.
 */
class RegisterController extends BaseController
{
    protected $helpers = ['setting'];

    /**
     * Displays the registration form.
     *
     * @return RedirectResponse|string
     */
    public function registerView()
    {
        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()->withInput()
                ->with('error', lang('Auth.registerDisabled'));
        }

        return view(setting('Auth.views')['register']);
    }

    /**
     * Attempts to register the user.
     */
    public function registerAction(): RedirectResponse
    {
        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()->withInput()
                ->with('error', lang('Auth.registerDisabled'));
        }

        $users = $this->getUserProvider();

        // Validate here first, since some things,
        // like the password, can only be validated properly here.
        $rules = $this->getValidationRules();

        /** @var Validation $validation */
        $validation = service('validation');

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Save the user
        $allowedPostFields = array_merge(setting('Auth.validFields'), setting('Auth.personalFields'));
        $user              = $this->getUserEntity();

        $user->fill($this->request->getPost($allowedPostFields));

        if (! $users->save($user)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        // Get the updated user so we have the ID...
        $user = $users->find($users->getInsertID());

        // Store the email/password identity for this user.
        $user->createEmailIdentity($this->request->getPost(['email', 'password']));

        // Add to default group
        $users->addToDefaultGroup($user);

        Events::trigger('didRegister', $user);

        auth()->login($user);

        // If an action has been defined for login, start it up.
        $actionClass = setting('Auth.actions')['register'] ?? null;

        if (! empty($actionClass)) {
            session()->set('auth_action', $actionClass);

            return redirect()->to('auth/a/show');
        }

        // Set the user active
        $user->active = true;
        $users->save($user);

        // Success!
        return redirect()->to(config('Auth')->registerRedirect())
            ->with('message', lang('Auth.registerSuccess'));
    }

    /**
     * Returns the User provider
     */
    protected function getUserProvider(): UserProvider
    {
        $provider = model(setting('Auth.userProvider'));

        assert($provider instanceof UserProvider, 'Config Auth.userProvider is not a valid UserProvider.');

        return $provider;
    }

    /**
     * Returns the Entity class that should be used
     */
    protected function getUserEntity(): User
    {
        return new User();
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return string[]
     */
    protected function getValidationRules(): array
    {
        return [
            'username'         => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
            'email'            => 'required|valid_email|is_unique[auth_identities.secret]',
            'password'         => 'required|strong_password',
            'password_confirm' => 'required|matches[password]',
        ];
    }
}
