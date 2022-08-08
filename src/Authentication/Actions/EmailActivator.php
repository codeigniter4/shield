<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\UserIdentityModel;

class EmailActivator implements ActionInterface
{
    private string $type = Session::ID_TYPE_EMAIL_ACTIVATE;

    /**
     * Shows the initial screen to the user telling them
     * that an email was just sent to them with a link
     * to confirm their email address.
     */
    public function show(): string
    {
        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $user = $authenticator->getPendingUser();
        if ($user === null) {
            throw new RuntimeException('Cannot get the pending login User.');
        }

        $userEmail = $user->email;
        if ($userEmail === null) {
            throw new LogicException(
                'Email Activation needs user email address. user_id: ' . $user->id
            );
        }

        $code = $this->createIdentity($user);

        // Send the email
        helper('email');
        $return = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($userEmail)
            ->setSubject(lang('Auth.emailActivateSubject'))
            ->setMessage(view(setting('Auth.views')['action_email_activate_email'], ['code' => $code]))
            ->send();

        if ($return === false) {
            throw new RuntimeException('Cannot send email for user: ' . $user->email);
        }

        // Display the info page
        return view(setting('Auth.views')['action_email_activate_show'], ['user' => $user]);
    }

    /**
     * This method is unused.
     */
    public function handle(IncomingRequest $request)
    {
        throw new PageNotFoundException();
    }

    /**
     * Verifies the email address and code matches an
     * identity we have for that user.
     *
     * @return RedirectResponse|string
     */
    public function verify(IncomingRequest $request)
    {
        $token = $request->getVar('token');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // No match - let them try again.
        if (! $authenticator->checkAction($this->type, $token)) {
            session()->setFlashdata('error', lang('Auth.invalidActivateToken'));

            return view(setting('Auth.views')['action_email_activate_show']);
        }

        $user = $authenticator->getUser();

        // Set the user active now
        $authenticator->activateUser($user);

        // Success!
        return redirect()->to(config('Auth')->registerRedirect())
            ->with('message', lang('Auth.registerSuccess'));
    }

    /**
     * Called from `RegisterController::registerAction()`
     */
    public function afterRegister(User $user): void
    {
        $this->createIdentity($user);
    }

    final protected function createIdentity(User $user): string
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $generator = static fn (): string => random_string('nozero', 6);

        return $identityModel->createCodeIdentity(
            $user,
            [
                'type'  => $this->type,
                'name'  => 'register',
                'extra' => lang('Auth.needVerification'),
            ],
            $generator
        );
    }

    public function getType(): string
    {
        return $this->type;
    }
}
