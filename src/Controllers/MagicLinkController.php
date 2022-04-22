<?php

namespace CodeIgniter\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Interfaces\UserProvider;
use CodeIgniter\Shield\Models\UserIdentityModel;

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
    public function loginView(): string
    {
        return view(setting('Auth.views')['magic-link-login']);
    }

    /**
     * Receives the email from the user, creates the hash
     * to a user identity, and sends an email to the given
     * email address.
     *
     * @return RedirectResponse|string
     */
    public function loginAction()
    {
        $email = $this->request->getPost('email');
        $user  = $this->provider->findByCredentials(['email' => $email]);

        if (empty($email) || $user === null) {
            return redirect()->route('magic-link')->with('error', lang('Auth.invalidEmail'));
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // Delete any previous magic-link identities
        $identityModel->deleteIdentitiesByType($user->getAuthId(), 'magic-link');

        // Generate the code and save it as an identity
        helper('text');
        $token = random_string('crypto', 20);

        $identityModel->insert([
            'user_id' => $user->getAuthId(),
            'type'    => 'magic-link',
            'secret'  => $token,
            'expires' => Time::now()->addSeconds(setting('Auth.magicLinkLifetime'))->toDateTimeString(),
        ]);

        // Send the user an email with the code
        helper('email');
        $email = emailer();
        $email->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '')
            ->setTo($user->getAuthEmail())
            ->setSubject(lang('Auth.magicLinkSubject'))
            ->setMessage(view(setting('Auth.views')['magic-link-email'], ['token' => $token]))
            ->send();

        return $this->displayMessage();
    }

    /**
     * Display the "What's happening/next" message to the user.
     */
    protected function displayMessage(): string
    {
        return view(setting('Auth.views')['magic-link-message']);
    }

    /**
     * Handles the GET request from the email
     */
    public function verify(): RedirectResponse
    {
        $token = $this->request->getGet('token');

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identity = $identityModel->getIdentityBySecret('magic-link', $token);

        // No token found?
        if ($identity === null) {
            return redirect()->route('magic-link')->with('error', lang('Auth.magicTokenNotFound'));
        }

        // Delete the db entry so it cannot be used again.
        $identityModel->delete($identity->id);

        // Token expired?
        if (Time::now()->isAfter($identity->expires)) {
            return redirect()->route('magic-link')->with('error', lang('Auth.magicLinkExpired'));
        }

        /** @var Auth $auth */
        $auth = service('auth');

        // Log the user in
        $auth->loginById($identity->user_id);

        // Get our login redirect url
        return redirect()->to(config('Auth')->loginRedirect());
    }
}
