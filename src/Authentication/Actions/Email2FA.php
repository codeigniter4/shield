<?php

namespace Sparks\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;
use Sparks\Shield\Controllers\LoginController;
use Sparks\Shield\Models\UserIdentityModel;

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
     *
     * @return mixed
     */
    public function show()
    {
        echo view(setting('Auth.views')['action_email_2fa']);
    }

    /**
     * Generates the random number, saves it as a temp identity
     * with the user, and fires off an email to the user with the code,
     * then displays the form to accept the 6 digits
     *
     * @return mixed
     */
    public function handle(IncomingRequest $request)
    {
        $email = $request->getPost('email');
        $user  = auth()->user();

        if (empty($email) || $email !== $user->email) {
            return redirect()->to(route_to('auth-action-show'))->with('error', lang('Auth.invalidEmail'));
        }

        // Delete any previous email_2fa identities
        $identities = new UserIdentityModel();
        $identities->where('user_id', $user->id)
            ->where('type', 'email_2fa')
            ->delete();

        // Generate the code and save it as an identity
        helper('text');
        $code = random_string('nozero', 6);

        $identities->insert([
            'user_id' => $user->id,
            'type'    => 'email_2fa',
            'secret'  => $code,
        ]);

        // Send the user an email with the code
        helper('email');
        $email = emailer();
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->email)
            ->setSubject(lang('Auth.email2FASubject'))
            ->setMessage(view(setting('Auth.views')['action_email_2fa_email'], ['code' => $code]))
            ->send();

        echo view(setting('Auth.views')['action_email_2fa_verify']);
    }

    /**
     * Attempts to verify the code the user entered.
     *
     * @return mixed
     */
    public function verify(IncomingRequest $request)
    {
        $token    = $request->getPost('token');

        if(setting('Auth.allowEmail2FALoginWithLink') && $request->getGet(setting('Auth.allowEmail2FALoginFieldName'))) {
            $token = $request->getGet(setting('Auth.allowEmail2FALoginFieldName'));
        }

        $user     = auth()->user();
        $identity = $user->getIdentity('email_2fa');

        // Token mismatch? Let them try again...
        if (empty($token) || $token !== $identity->secret) {
            session()->set('error',lang('Auth.invalid2FAToken'));
            redirect()->to(route_to('auth-action-handle'));
        }

        // On success - remove the identity and clean up session
        model(UserIdentityModel::class)
            ->where('user_id', $user->id)
            ->where('type', 'email_2fa')
            ->delete();

        // Clean up our session
        session()->remove('auth_action');

        // Get our login redirect url
        $loginController = new LoginController();

        return redirect()->to($loginController->getLoginRedirect($user));
    }
}
