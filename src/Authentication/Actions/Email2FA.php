<?php

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\UserIdentityModel;

/**
 * Class Email2FA
 *
 * Sends an email to the user with a code to verify their account.
 */
class Email2FA implements ActionInterface
{
    private string $type = Session::ID_TYPE_EMAIL_2FA;

    /**
     * Displays the "Hey we're going to send you a number to your email"
     * message to the user with a prompt to continue.
     */
    public function show(): string
    {
        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        $user = $authenticator->getPendingUser();
        if ($user === null) {
            throw new RuntimeException('Cannot get the pending login User.');
        }

        $this->createIdentity($user);

        return view(setting('Auth.views')['action_email_2fa'], ['user' => $user]);
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

        $user = $authenticator->getPendingUser();
        if ($user === null) {
            throw new RuntimeException('Cannot get the pending login User.');
        }

        if (empty($email) || $email !== $user->email) {
            return redirect()->route('auth-action-show')->with('error', lang('Auth.invalidEmail'));
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identity = $identityModel->getIdentityByType($user, $this->type);

        if (empty($identity)) {
            return redirect()->route('auth-action-show')->with('error', lang('Auth.need2FA'));
        }

        // Send the user an email with the code
        helper('email');
        $return = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->email)
            ->setSubject(lang('Auth.email2FASubject'))
            ->setMessage(view(setting('Auth.views')['action_email_2fa_email'], ['code' => $identity->secret]))
            ->send();

        if ($return === false) {
            throw new RuntimeException('Cannot send email for user: ' . $user->email);
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
        if (! $authenticator->checkAction($this->type, $token)) {
            session()->setFlashdata('error', lang('Auth.invalid2FAToken'));

            return view(setting('Auth.views')['action_email_2fa_verify']);
        }

        // Get our login redirect url
        return redirect()->to(config('Auth')->loginRedirect());
    }

    /**
     * Called from `Session::attempt()`.
     */
    public function afterLogin(User $user): void
    {
        $this->createIdentity($user);
    }

    final protected function createIdentity(User $user): void
    {
        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $generator = static fn (): string => random_string('nozero', 6);

        $identityModel->createCodeIdentity(
            $user,
            [
                'type'  => $this->type,
                'name'  => 'login',
                'extra' => lang('Auth.need2FA'),
            ],
            $generator
        );
    }

    public function getType(): string
    {
        return $this->type;
    }
}
