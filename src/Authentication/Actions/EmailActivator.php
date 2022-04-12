<?php

namespace Sparks\Shield\Authentication\Actions;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use Sparks\Shield\Controllers\LoginController;
use Sparks\Shield\Models\UserIdentityModel;

class EmailActivator implements ActionInterface
{
    /**
     * Shows the initial screen to the user telling them
     * that an email was just sent to them with a link
     * to confirm their email address.
     *
     * @return mixed
     */
    public function show()
    {
        $userId = session()->get(setting('Auth.sessionConfig')['fieldRegister']);

        //  Create an identity for our activation hash
        $identities = new UserIdentityModel();
        $identities->where('user_id', $userId)
            ->where('type', 'email_activate')
            ->delete();

        // Generate the code and save it as an identity
        helper('text');
        $code = random_string('nozero', 6);

        $identities->insert([
            'user_id' => $userId,
            'type'    => 'email_activate',
            'secret'  => $code,
        ]);

        $user = model(setting('Auth.userProvider'))->find($userId);
        
        // Send the email
        helper('email');
        $email = emailer();
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->email, $user->username)
            ->setSubject(lang('Auth.emailActivateSubject'))
            ->setMessage(view(setting('Auth.views')['action_email_activate_email'], ['code' => $code]))
            ->send();

        // Display the info page
        echo view(setting('Auth.views')['action_email_activate_show'], ['user' => $user]);
    }

    /**
     * This method is unused.
     *
     * @return mixed
     */
    public function handle(IncomingRequest $request)
    {
        throw new PageNotFoundException();
    }

    /**
     * Verifies the email address and code matches an
     * identity we have for that user.
     *
     * @return mixed
     */
    public function verify(IncomingRequest $request)
    {
        $token = $request->getVar('token');
        
        if(!($userId = session()->get(setting('Auth.sessionConfig')['fieldRegister']))) {
            session()->set('error', lang('Auth.activationLinkIsNotLongerValid'));
            // Get our login redirect url
            $loginController = new LoginController();
            
            return redirect()->to($loginController->getLoginRedirect());
        }

        $user = model(setting('Auth.userProvider'))->find($userId);
        $identity = $user->getIdentity('email_activate');

        // No match - let them try again.
        if ($identity->secret !== $token) {
            $_SESSION['error'] = lang('Auth.invalidActivateToken');

            return view(setting('Auth.views')['action_email_activate_show']);
        }

        // Remove the identity
        $identities = new UserIdentityModel();
        $identities->delete($identity->id);

        // Clean up our session
        unset($_SESSION['auth_action']);

        // Get our login redirect url
        $loginController = new LoginController();

        return redirect()->to($loginController->getLoginRedirect($user));
    }
}
