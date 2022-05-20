<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\DatabaseException;
use CodeIgniter\Shield\Models\UserIdentityModel;

class EmailActivator implements ActionInterface
{
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

        $code = $this->createIdentity($user, 'register', lang('Auth.needVerification'));

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
        $this->createIdentity($user, 'register', lang('Auth.needVerification'));
    }

    /**
     * Create an identity for Email Activation
     *
     * @return string The secret code
     */
    private function createIdentity(User $user, string $name, string $extra): string
    {
        helper('text');

        /** @var UserIdentityModel $userIdentityModel */
        $userIdentityModel = model(UserIdentityModel::class);

        // Delete any previous activation identities
        $userIdentityModel->deleteIdentitiesByType($user, $this->type);

        // Create an identity for our activation hash
        $maxTry = 5;
        $data   = [
            'user_id' => $user->getAuthId(),
            'type'    => $this->type,
            'name'    => $name,
            'extra'   => $extra,
        ];

        while (true) {
            $data['secret'] = $this->generateSecretCode();

            try {
                $userIdentityModel->create($data);

                break;
            } catch (DatabaseException $e) {
                $maxTry--;

                if ($maxTry === 0) {
                    throw $e;
                }
            }
        }

        return $data['secret'];
    }

    private function generateSecretCode(): string
    {
        return random_string('nozero', 6);
    }

    public function getType(): string
    {
        return $this->type;
    }
}
