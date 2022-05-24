<?php

namespace Sparks\Shield\Authentication\Actions;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
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
        $return = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->getAuthEmail())
            ->setSubject(lang('Auth.emailActivateSubject'))
            ->setMessage(view(setting('Auth.views')['action_email_activate_email'], ['code' => $code]))
            ->send();

        if ($return === false) {
            throw new RuntimeException('Cannot send email for user: ' . $user->getAuthEmail());
        }

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

        // No match - let them try again.
        if (! auth()->checkAction('email_activate', $token)) {
            session()->setFlashdata('error', lang('Auth.invalidActivateToken'));

            return view(setting('Auth.views')['action_email_activate_show']);
        }

        $user = auth()->user();

        // Set the user active now
        $provider     = auth()->getProvider();
        $user->active = true;
        $provider->save($user);

        // Success!
        return redirect()->to(config('Auth')->registerRedirect())
            ->with('message', lang('Auth.registerSuccess'));
    }
}
