<?php

namespace Sparks\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;
use Sparks\Shield\Interfaces\UserProvider;
use Sparks\Shield\Models\UserIdentityModel;

/**
 * Handles "Magic Link" logins - an email-based
 * no-password login protocol. This works much
 * like password reset would, but Shield provides
 * this in place of password reset. It can also
 * be used on it's own without an email/password
 * login strategy.
 */
class MagicLinkController extends BaseController
{
    /**
     * @var UserProvider
     */
    protected $provider;

    public function __construct()
    {
        helper('setting');
        $providerClass  = setting('Auth.userProvider');
        $this->provider = new $providerClass();
    }

    /**
     * Displays the view to enter their email address
     * so an email can be sent to them.
     */
    public function loginView()
    {
        echo view(setting('Auth.views')['magic-link-login']);
    }

    /**
     * Receives the email from the user, creates the hash
     * to a user identity, and sends an email to the given
     * email address.
     */
    public function loginAction()
    {
        $email = $this->request->getPost('email');
        $user  = $this->provider->findByCredentials(['email' => $email]);

        if (empty($email) || $user === null) {
            return redirect()->route('magic-link')->with('error', lang('Auth.invalidEmail'));
        }

        // Delete any previous magic-link identities
        $identities = new UserIdentityModel();
        $identities->where('user_id', $user->id)
            ->where('type', 'magic-link')
            ->delete();

        // Generate the code and save it as an identity
        helper('text');
        $token = random_string('crypto', 20);

        $identities->insert([
            'user_id' => $user->id,
            'type'    => 'magic-link',
            'secret'  => $token,
            'expires' => Time::now()->addSeconds(setting('Auth.magicLinkLifetime'))->toDateTimeString(),
        ]);

        // Send the user an email with the code
        $email = service('email');
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName'))
            ->setTo($user->email)
            ->setSubject(lang('Auth.magicLinkSubject'))
            ->setMessage(view(setting('Auth.views')['magic-link-email'], ['token' => $token]))
            ->send();

        echo $this->displayMessage();
    }

    /**
     * Display the "What's happening/next" message to the user.
     *
     * @return string
     */
    protected function displayMessage()
    {
        return view(setting('Auth.views')['magic-link-message']);
    }

    /**
     * Handles the GET request from the email
     *
     * @returns RedirectResponse
     */
    public function verify()
    {
        $token      = $this->request->getGet('token');
        $identities = model(UserIdentityModel::class);
        $identity   = $identities
            ->where('type', 'magic-link')
            ->where('secret', $token)
            ->first();

        // No token found?
        if ($identity === null) {
            return redirect()->route('magic-link')->with('error', lang('Auth.magicTokenNotFound'));
        }

        // Delete the db entry so it cannot be used again.
        $identities->delete($identity->id);

        // Token expired?
        if (Time::now()->isAfter($identity->expires)) {
            return redirect()->route('magic-link')->with('error', lang('Auth.magicLinkExpired'));
        }

        // Log the user in
        $auth = service('auth');
        $auth->loginById($identity->user_id);

        // Get our login redirect url
        $loginController = new LoginController();

        return redirect()->to($loginController->getLoginRedirect($identity->user_id));
    }
}
