<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Models\UserIdentityModel;

/**
 * Class Email2FA
 *
 * Sends an email to the user with a code to verify their account.
 */
class Email2FA implements ActionInterface
{
    /**
     * Displays the "Hey we're going to send you an number to your email"
     * message to the user with a prompt to continue.
     */
    public function show(): string
    {
        $user = auth()->user();

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // Delete any previous activation identities
        $identityModel->deleteIdentitiesByType($user->getAuthId(), 'email_2fa');

        // Create an identity for our 2fa hash
        helper('text');
        $code = random_string('nozero', 6);

        $identityModel->insert([
            'user_id' => $user->getAuthId(),
            'type'    => 'email_2fa',
            'secret'  => $code,
            'name'    => 'login',
            'extra'   => lang('Auth.need2FA'),
        ]);

        return view(setting('Auth.views')['action_email_2fa']);
    }

    /**
     * Generates the random number, saves it as a temp identity
     * with the user, and fires off an email to the user with the code,
     * then displays the form to accept the 6 digits
     *
     * @return RedirectResponse|string
     */
    public function handle(IncomingRequest $request)
    {
        $email = $request->getPost('email');
        $user  = auth()->user();

        if (empty($email) || $email !== $user->getAuthEmail()) {
            return redirect()->route('auth-action-show')->with('error', lang('Auth.invalidEmail'));
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identity = $identityModel->getIdentityByType($user->getAuthId(), 'email_2fa');

        if (empty($identity)) {
            return redirect()->route('auth-action-show')->with('error', lang('Auth.need2FA'));
        }

        // Send the user an email with the code
        helper('email');
        $email = emailer();
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->getAuthEmail())
            ->setSubject(lang('Auth.email2FASubject'))
            ->setMessage(view(setting('Auth.views')['action_email_2fa_email'], ['code' => $identity->secret]))
            ->send();

        return view(setting('Auth.views')['action_email_2fa_verify']);
    }

    /**
     * Attempts to verify the code the user entered.
     *
     * @return RedirectResponse|string
     */
    public function verify(IncomingRequest $request)
    {
        $token    = $request->getPost('token');
        $user     = auth()->user();
        $identity = $user->getIdentity('email_2fa');

        // Token mismatch? Let them try again...
        if (empty($token) || $token !== $identity->secret) {
            $_SESSION['error'] = lang('Auth.invalid2FAToken');

            return view(setting('Auth.views')['action_email_2fa_verify']);
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // On success - remove the identity and clean up session
        $identityModel->deleteIdentitiesByType($user->getAuthId(), 'email_2fa');

        // Clean up our session
        session()->remove('auth_action');

        // Get our login redirect url
        return redirect()->to(config('Auth')->loginRedirect());
    }
}
