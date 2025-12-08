<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Traits\Viewable;

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
    use Viewable;

    /**
     * @var UserModel
     */
    protected $provider;

    public function __construct()
    {
        /** @var class-string<UserModel> $providerClass */
        $providerClass = setting('Auth.userProvider');

        $this->provider = new $providerClass();
    }

    /**
     * Displays the view to enter their email address
     * so an email can be sent to them.
     *
     * @return RedirectResponse|string
     */
    public function loginView()
    {
        if (! setting('Auth.allowMagicLinkLogins')) {
            return redirect()->route('login')->with('error', lang('Auth.magicLinkDisabled'));
        }

        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->loginRedirect());
        }

        return $this->view(setting('Auth.views')['magic-link-login']);
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
        if (! setting('Auth.allowMagicLinkLogins')) {
            return redirect()->route('login')->with('error', lang('Auth.magicLinkDisabled'));
        }

        // Validate email format
        $rules = $this->getValidationRules();
        if (! $this->validateData($this->request->getPost(), $rules, [], config('Auth')->DBGroup)) {
            return redirect()->route('magic-link')->with('errors', $this->validator->getErrors());
        }

        // Check if the user exists
        $email = $this->request->getPost('email');
        $user  = $this->provider->findByCredentials(['email' => $email]);

        if ($user === null) {
            return redirect()->route('magic-link')->with('error', lang('Auth.invalidEmail', [$email]));
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // Delete any previous magic-link identities
        $identityModel->deleteIdentitiesByType($user, Session::ID_TYPE_MAGIC_LINK);

        $mode = $this->resolveMode();

        $token = $mode['token'];

        $identityModel->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => $token,
            'expires' => Time::now()->addSeconds(setting('Auth.magicLinkLifetime')),
        ]);

        /** @var IncomingRequest $request */
        $request = service('request');

        $ipAddress = $request->getIPAddress();
        $userAgent = (string) $request->getUserAgent();
        $date      = Time::now()->toDateTimeString();

        // Send the user an email with the code
        helper('email');
        $email = emailer(['mailType' => 'html'])
            ->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
        $email->setTo($user->email);

        $email->setSubject($mode['emailSubject']);

        $email->setMessage($this->view(
            setting('Auth.views')[$mode['emailView']],
            ['token' => $token, 'user' => $user, 'ipAddress' => $ipAddress, 'userAgent' => $userAgent, 'date' => $date],
            ['debug' => false],
        ));

        if ($email->send(false) === false) {
            log_message('error', $email->printDebugger(['headers']));

            return redirect()->route('magic-link')->with('error', lang('Auth.unableSendEmailToUser', [$user->email]));
        }

        // Clear the email
        $email->clear();

        return $this->displayMessage();
    }

    /**
     * Display the "What's happening/next" message to the user.
     */
    protected function displayMessage(): string
    {
        $viewFile = $this->resolveMode()['displayMessageView'];

        return $this->view(config('Auth')->views[$viewFile]);
    }

    /**
     * Handles the GET request from the email
     */
    public function verify(): RedirectResponse
    {
        if (! setting('Auth.allowMagicLinkLogins')) {
            return redirect()->route('login')->with('error', lang('Auth.magicLinkDisabled'));
        }

        if ($this->request->getUserAgent()->isRobot()) {
            throw PageNotFoundException::forPageNotFound();
        }

        $identifier = $this->request->getGet('token');

        if ($this->request->is('post')) {
            $identifier = $this->request->getPost('magicCode');
        }

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        $identity = $identityModel->getIdentityBySecret(Session::ID_TYPE_MAGIC_LINK, $identifier);

        $identifier ??= '';

        // No token found?
        if ($identity === null) {
            $this->recordLoginAttempt($identifier, false);

            $credentials = ['magicLinkToken' => $identifier];
            Events::trigger('failedLogin', $credentials);

            return redirect()->route('magic-link')->with('error', lang('Auth.magicTokenNotFound'));
        }

        // Delete the db entry so it cannot be used again.
        $identityModel->delete($identity->id);

        // Token expired?
        if (Time::now()->isAfter($identity->expires)) {
            $this->recordLoginAttempt($identifier, false);

            $credentials = ['magicLinkToken' => $identifier];
            Events::trigger('failedLogin', $credentials);

            return redirect()->route('magic-link')->with('error', lang('Auth.magicLinkExpired'));
        }

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // If an action has been defined
        if ($authenticator->hasAction($identity->user_id)) {
            return redirect()->route('auth-action-show')->with('error', lang('Auth.needActivate'));
        }

        // Log the user in
        $authenticator->loginById($identity->user_id);

        $user = $authenticator->getUser();

        $this->recordLoginAttempt($identifier, true, $user->id);

        // Give the developer a way to know the user
        // logged in via a magic link.
        session()->setTempdata('magicLogin', true);

        Events::trigger('magicLogin');

        // Get our login redirect url
        return redirect()->to(config('Auth')->loginRedirect());
    }

    /**
     * @param int|string|null $userId
     */
    private function recordLoginAttempt(
        string $identifier,
        bool $success,
        $userId = null,
    ): void {
        /** @var LoginModel $loginModel */
        $loginModel = model(LoginModel::class);

        $loginModel->recordLoginAttempt(
            Session::ID_TYPE_MAGIC_LINK,
            $identifier,
            $success,
            $this->request->getIPAddress(),
            (string) $this->request->getUserAgent(),
            $userId,
        );
    }

    /**
     * Returns the rules that should be used for validation.
     *
     * @return array<string, array<string, list<string>|string>>
     */
    protected function getValidationRules(): array
    {
        return [
            'email' => config('Auth')->emailValidationRules,
        ];
    }

    /**
     * resolveMode magic-login settings based on the configured mode.
     *
     * @param string $mode The selected magic login mode (e.g. "clickable", "6-numeric").
     *
     * @throws InvalidArgumentException
     */
    protected function resolveMode(?string $mode = null): array
    {
        $mode ??= config('Auth')->magicLoginMode;

        helper('text');

        if ($mode === 'clickable') {
            return [
                'displayMessageView' => 'magic-link-message',
                'emailView'          => 'magic-link-email',
                'emailSubject'       => lang('Auth.magicLinkSubject'),
                'token'              => random_string('crypto', 20),
            ];
        }

        $parts = explode('-', $mode, 2);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException(
                "Invalid magic login mode format '{$mode}'. Expected format: '<length>-<numeric|alpha|alnum|oneof>' or 'clickable'.",
            );
        }

        [$length, $type] = $parts;

        if (! is_numeric($length) || (int) $length <= 0) {
            throw new InvalidArgumentException(
                "Invalid length '{$length}' in magic login mode '{$mode}'. Must be a positive integer.",
            );
        }

        $length = (int) $length;

        return match ($type) {
            'numeric', 'alpha', 'alnum' => [
                'displayMessageView' => 'magic-link-code',
                'emailView'          => 'magic-link-email-code',
                'emailSubject'       => lang('Auth.magicCodeSubject'),
                'token'              => random_string($type, $length),
            ],

            'oneof' => [
                'displayMessageView' => 'magic-link-code',
                'emailView'          => 'magic-link-email-code',
                'emailSubject'       => lang('Auth.magicCodeSubject'),
                'token'              => $this->generateOneofToken($length),
            ],

            default => throw new InvalidArgumentException("Invalid magic login mode '{$mode}'. Expected format: '<length>-<numeric|alpha|alnum|oneof>'."),
        };
    }

    /**
     * Generate a token by picking ONE of the fixed patterns: numeric, alpha, alnum.
     */
    private function generateOneofToken(int $length): string
    {
        helper('text');

        $patterns   = ['numeric', 'alpha', 'alnum'];
        $chosenMode = $patterns[array_rand($patterns)];

        return random_string($chosenMode, $length);
    }
}
