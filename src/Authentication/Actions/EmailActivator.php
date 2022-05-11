<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Models\UserIdentityModel;

class EmailActivator implements ActionInterface
{
    /**
     * Shows the initial screen to the user telling them
     * that an email was just sent to them with a link
     * to confirm their email address.
     */
    public function show(): string
    {
        $user = auth()->user();

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // Delete any previous activation identities
        $identityModel->deleteIdentitiesByType($user->getAuthId(), 'email_activate');

        //  Create an identity for our activation hash
        helper('text');
        $code = random_string('nozero', 6);

        $identityModel->insert([
            'user_id' => $user->getAuthId(),
            'type'    => 'email_activate',
            'secret'  => $code,
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);

        // Send the email
        helper('email');
        $email = emailer();
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->getAuthEmail())
            ->setSubject(lang('Auth.emailActivateSubject'))
            ->setMessage(view(setting('Auth.views')['action_email_activate_email'], ['code' => $code]))
            ->send();

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
        $token    = $request->getVar('token');
        $user     = auth()->user();
        $identity = $user->getIdentity('email_activate');

        // No match - let them try again.
        if ($identity->secret !== $token) {
            $_SESSION['error'] = lang('Auth.invalidActivateToken');

            return view(setting('Auth.views')['action_email_activate_show']);
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // Remove the identity
        $identityModel->delete($identity->id);

        // Set the user active now
        $provider     = auth()->getProvider();
        $user->active = true;
        $provider->save($user);

        // Clean up our session
        unset($_SESSION['auth_action']);

        // Get our login redirect url
        return redirect()->to(config('Auth')->loginRedirect());
    }
}
