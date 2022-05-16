<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Exceptions\RuntimeException;
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
        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $user = $authenticator->getUser();

        $authenticator->createIdentityEmail2FA();

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

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $user = $authenticator->getUser();

        if (empty($email) || $email !== $user->getAuthEmail()) {
            return redirect()->route('auth-action-show')->with('error', lang('Auth.invalidEmail'));
        }

        if ($user === null) {
            throw new RuntimeException('Cannot get the User.');
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identity = $identityModel->getIdentityByType($user->getAuthId(), 'email_2fa');

        if (empty($identity)) {
            return redirect()->route('auth-action-show')->with('error', lang('Auth.need2FA'));
        }

        // Send the user an email with the code
        helper('email');
        $return = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->getAuthEmail())
            ->setSubject(lang('Auth.email2FASubject'))
            ->setMessage(view(setting('Auth.views')['action_email_2fa_email'], ['code' => $identity->secret]))
            ->send();

        if ($return === false) {
            throw new RuntimeException('Cannot send email for user: ' . $user->getAuthEmail());
        }

        return view(setting('Auth.views')['action_email_2fa_verify']);
    }

    /**
     * Attempts to verify the code the user entered.
     *
     * @return RedirectResponse|string
     */
    public function verify(IncomingRequest $request)
    {
        $token = $request->getPost('token');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // Token mismatch? Let them try again...
        if (! $authenticator->checkAction('email_2fa', $token)) {
            session()->setFlashdata('error', lang('Auth.invalid2FAToken'));

            return view(setting('Auth.views')['action_email_2fa_verify']);
        }

        // Get our login redirect url
        return redirect()->to(config('Auth')->loginRedirect());
    }
}
