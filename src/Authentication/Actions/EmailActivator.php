<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Exceptions\RuntimeException;

class EmailActivator implements ActionInterface
{
    use CreateIdentityTrait;

    private string $type = 'email_activate';

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

        $userEmail = $user->getAuthEmail();
        if ($userEmail === null) {
            throw new LogicException(
                'Email Activation needs user email address. user_id: ' . $user->getAuthId()
            );
        }

        $code = $this->createCodeIdentity($user, 'register', lang('Auth.needVerification'));

        // Send the email
        helper('email');
        $return = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($userEmail)
            ->setSubject(lang('Auth.emailActivateSubject'))
            ->setMessage(view(setting('Auth.views')['action_email_activate_email'], ['code' => $code]))
            ->send();

        if ($return === false) {
            throw new RuntimeException('Cannot send email for user: ' . $user->getAuthEmail());
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

        // Get our login redirect url
        return redirect()->to(config('Auth')->loginRedirect());
    }

    /**
     * Called from `RegisterController::registerAction()`
     */
    public function afterRegister(User $user): void
    {
        $this->createCodeIdentity($user, 'register', lang('Auth.needVerification'));
    }

    public function getType(): string
    {
        return $this->type;
    }
}
