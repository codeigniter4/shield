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

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\AdminEmail2FA;
use Tests\Support\AdminEmailActivator;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class MagicLinkTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use FakeUser;

    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        // Add auth routes
        $routes = service('routes');
        auth()->routes($routes);
        Services::injectMock('routes', $routes);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up any robot user agent set in tests
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testAfterLoggedInNotAllowDisplayMagicLink(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->get('/login/magic-link');
        $result->assertRedirectTo(config('Auth')->loginRedirect());
    }

    public function testShowValidateErrorsInMagicLink(): void
    {
        $result = $this->post('/login/magic-link', [
            'email' => 'foo@example',
        ]);

        $expected = ['email' => 'The Email Address field must contain a valid email address.'];

        $result->assertSessionHas('errors', $expected);
    }

    /**
     * @see https://github.com/codeigniter4/shield/issues/465
     */
    public function testMagicLinkVerifyPendingRegistrationActivation(): void
    {
        // Enable Register action (Email Activation)
        $config                      = config('Auth');
        $config->actions['register'] = EmailActivator::class;
        Factories::injectMock('config', 'Auth', $config);

        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $identities = model(UserIdentityModel::class);

        // Insert User Identity for Email Activation
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_EMAIL_ACTIVATE,
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
        // Insert User Identity for Magic link login
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => 'abasdasdf',
            'expires' => Time::now()->addMinutes(60),
        ]);

        $result = $this->get(route_to('verify-magic-link') . '?token=abasdasdf');

        $result->assertRedirectTo(route_to('auth-action-show'));
        $result->assertSessionHas(
            'error',
            lang('Auth.needActivate'),
        );
        $result->assertSessionHas(
            'user',
            [
                'id'                  => $user->id,
                'auth_action'         => 'CodeIgniter\Shield\Authentication\Actions\EmailActivator',
                'auth_action_message' => lang('Auth.needVerification'),
            ],
        );
        $this->assertFalse(auth()->loggedIn());
    }

    public function testMagicLinkVerifyPendingConditionalRegistrationActivation(): void
    {
        $config                      = config('Auth');
        $config->actions['register'] = AdminEmailActivator::class;
        Factories::injectMock('config', 'Auth', $config);

        /** @var User $user */
        $user = fake(UserModel::class, ['active' => false]);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $identities = model(UserIdentityModel::class);

        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_EMAIL_ACTIVATE,
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => 'validtoken123',
            'expires' => Time::now()->addMinutes(60),
        ]);

        $result = $this->get(route_to('verify-magic-link') . '?token=validtoken123');

        $result->assertRedirectTo(route_to('auth-action-show'));
        $result->assertSessionHas('error', lang('Auth.needActivate'));
        $result->assertSessionHas(
            'user',
            [
                'id'                  => $user->id,
                'auth_action'         => AdminEmailActivator::class,
                'auth_action_message' => lang('Auth.needVerification'),
            ],
        );
        $this->assertFalse(auth()->loggedIn());
    }

    public function testMagicLinkVerifyStartsLoginAction(): void
    {
        setting('Auth.actions', ['login' => Email2FA::class, 'register' => null]);

        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $this->insertMagicLinkIdentity($user, 'validtoken123');

        $result = $this->get(route_to('verify-magic-link') . '?token=validtoken123');

        $result->assertRedirect();
        $this->assertSame(site_url('/auth/a/show'), $result->getRedirectUrl());
        $this->assertPendingLoginAction($user, Email2FA::class);
        $this->seeInDatabase(config('Auth')->tables['identities'], [
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_EMAIL_2FA,
            'name'    => 'login',
        ]);
        $result->assertSessionMissing('magicLogin');
        $this->assertFalse(auth()->loggedIn());

        $identity = model(UserIdentityModel::class)->getIdentityByType($user, Session::ID_TYPE_EMAIL_2FA);
        $this->assertNotNull($identity);

        $result = $this->withSession()->post('/auth/a/verify', [
            'token' => $identity->secret,
        ]);

        $result->assertRedirectTo(config('Auth')->loginRedirect());
        $result->assertSessionHas('user', ['id' => $user->id]);
        $result->assertSessionHas('magicLogin', true);
        $this->assertTrue(auth()->loggedIn());
    }

    public function testMagicLinkVerifyStartsConditionalLoginActionWhenItApplies(): void
    {
        setting('Auth.actions', ['login' => AdminEmail2FA::class, 'register' => null]);

        /** @var User $user */
        $user = fake(UserModel::class);
        $user->addGroup('admin');
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $this->insertMagicLinkIdentity($user, 'validtoken123');

        $result = $this->get(route_to('verify-magic-link') . '?token=validtoken123');

        $result->assertRedirect();
        $this->assertSame(site_url('/auth/a/show'), $result->getRedirectUrl());
        $this->assertPendingLoginAction($user, AdminEmail2FA::class);
        $result->assertSessionMissing('magicLogin');
        $this->assertFalse(auth()->loggedIn());
    }

    public function testMagicLinkVerifySkipsConditionalLoginActionWhenItDoesNotApply(): void
    {
        setting('Auth.actions', ['login' => AdminEmail2FA::class, 'register' => null]);

        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $this->insertMagicLinkIdentity($user, 'validtoken123');

        $result = $this->get(route_to('verify-magic-link') . '?token=validtoken123');

        $result->assertRedirectTo(config('Auth')->loginRedirect());
        $result->assertSessionHas('user', ['id' => $user->id]);
        $result->assertSessionMissing('auth_action');
        $this->assertTrue(auth()->loggedIn());
        $this->dontSeeInDatabase(config('Auth')->tables['identities'], [
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_EMAIL_2FA,
        ]);
    }

    public function testBackToLoginLinkOnPage(): void
    {
        $result = $this->get('/login/magic-link');
        $this->assertStringContainsString(lang('Auth.backToLogin'), $result->getBody());
    }

    public function testMagicLinkRedirectsIfNotAllowed(): void
    {
        $config                       = config('Auth');
        $config->allowMagicLinkLogins = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->get('/login/magic-link');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas(
            'error',
            lang('Auth.magicLinkDisabled'),
        );
    }

    public function testMagicLinkActionRedirectsIfNotAllowed(): void
    {
        $config                       = config('Auth');
        $config->allowMagicLinkLogins = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->post('/login/magic-link');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas(
            'error',
            lang('Auth.magicLinkDisabled'),
        );
    }

    public function testMagicLinkVerifyRedirectsIfNotAllowed(): void
    {
        $config                       = config('Auth');
        $config->allowMagicLinkLogins = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->get('/login/verify-magic-link');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas(
            'error',
            lang('Auth.magicLinkDisabled'),
        );
    }

    public function testMagicLinkVerifyReturns404ForRobotUserAgent(): void
    {
        $this->expectException(PageNotFoundException::class);

        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $identities = model(UserIdentityModel::class);

        // Insert User Identity for Magic link login
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => 'validtoken123',
            'expires' => Time::now()->addMinutes(60),
        ]);

        // Simulate a robot user agent
        service('superglobals')->setServer('HTTP_USER_AGENT', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        $this->get(route_to('verify-magic-link') . '?token=validtoken123');
    }

    private function insertMagicLinkIdentity(User $user, string $token): void
    {
        model(UserIdentityModel::class)->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => $token,
            'expires' => Time::now()->addMinutes(60),
        ]);
    }

    /**
     * @param class-string $action
     */
    private function assertPendingLoginAction(User $user, string $action): void
    {
        $sessionUser = session('user');
        $this->assertIsArray($sessionUser);
        $this->assertSame($user->id, $sessionUser['id'] ?? null);
        $this->assertSame($action, $sessionUser['auth_action'] ?? null);
        $this->assertSame(lang('Auth.need2FA'), $sessionUser['auth_action_message'] ?? null);
    }
}
