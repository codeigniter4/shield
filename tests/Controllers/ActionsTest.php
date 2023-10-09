<?php

declare(strict_types=1);

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\DatabaseTestCase;
use Tests\Support\FakeUser;

/**
 * @internal
 */
final class ActionsTest extends DatabaseTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;
    use FakeUser;

    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure our actions are registered with the system
        $config                      = config('Auth');
        $config->actions['login']    = Email2FA::class;
        $config->actions['register'] = EmailActivator::class;
        Factories::injectMock('config', 'Auth', $config);

        // Add auth routes
        $routes = service('routes');
        auth()->routes($routes);
        Services::injectMock('routes', $routes);

        $_SESSION = [];

        $this->user->createEmailIdentity(['email' => 'johnsmith@example.com', 'password' => 'secret123']);
    }

    public function testActionShowNoneAvailable(): void
    {
        $this->expectException(PageNotFoundException::class);

        $result = $this->withSession([])->get('/auth/a/show');

        // Nothing found, it should die gracefully.
        $result->assertStatus(404);
    }

    public function testEmail2FAShow(): void
    {
        $this->insertIdentityEmal2FA();

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo())
            ->get('/auth/a/show');

        $result->assertStatus(200);
        // Should auto populate in the form
        $result->assertSee($this->user->email);
    }

    private function getSessionUserInfo(string $class = Email2FA::class): array
    {
        return [
            'user' => [
                'id'          => $this->user->id,
                'auth_action' => $class,
            ],
        ];
    }

    public function testEmail2FAHandleInvalidEmail(): void
    {
        $this->insertIdentityEmal2FA();

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo())
            ->post('/auth/a/handle', [
                'email' => 'foo@example.com',
            ]);

        $result->assertRedirect();
        $result->assertSame(site_url('/auth/a/show'), $result->getRedirectUrl());
        $result->assertSessionHas('error', lang('Auth.invalidEmail'));
    }

    private function insertIdentityEmal2FA(): void
    {
        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => Session::ID_TYPE_EMAIL_2FA,
            'secret'  => '123456',
            'name'    => 'login',
            'extra'   => lang('Auth.need2FA'),
        ]);
    }

    public function testEmail2FAHandleSendsEmail(): void
    {
        $this->insertIdentityEmal2FA();

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo())
            ->post('/auth/a/handle', [
                'email' => $this->user->email,
            ]);

        $result->assertStatus(200);
        $result->assertSee(lang('Auth.emailEnterCode'));

        // Should have sent an email with the code....
        $this->assertStringContainsString('Your authentication code is:', service('email')->archive['body']);
    }

    public function testEmail2FAVerifyFails(): void
    {
        $this->insertIdentityEmal2FA();

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo())
            ->post('/auth/a/verify', [
                'token' => '234567',
            ]);

        $result->assertStatus(200);
        $result->assertSee(lang('Auth.invalid2FAToken'));
    }

    public function testEmail2FAVerify(): void
    {
        $this->insertIdentityEmal2FA();

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo())
            ->post('/auth/a/verify', [
                'token' => '123456',
            ]);

        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Identity should have been removed
        $this->dontSeeInDatabase($this->tables['identities'], [
            'user_id' => $this->user->id,
            'type'    => Session::ID_TYPE_EMAIL_2FA,
        ]);

        // Session should have been cleared
        $result->assertSessionMissing('auth_action');
    }

    public function testShowEmail2FACreatesIdentity(): void
    {
        $this->insertIdentityEmal2FA();

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo())
            ->get('/auth/a/show');

        $result->assertOK();

        $this->seeInDatabase($this->tables['identities'], [
            'user_id' => $this->user->id,
            'type'    => Session::ID_TYPE_EMAIL_2FA,
            'name'    => 'login',
        ]);
    }

    public function testEmail2FACannotBeBypassed(): void
    {
        // Ensure filter is enabled for all routes
        $config                      = config('Filters');
        $config->globals['before'][] = 'session';
        Factories::injectMock('config', 'Filters', $config);

        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => Session::ID_TYPE_EMAIL_2FA,
            'secret'  => '123456',
            'name'    => 'login',
            'extra'   => lang('Auth.need2FA'),
        ]);

        // Try to visit any other page, skipping the 2FA
        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo())
            ->get('/');

        $result->assertRedirect();
        $this->assertSame(site_url('/auth/a/show'), $result->getRedirectUrl());
    }

    private function insertIdentityEmailActivate(): void
    {
        // An identity with Email activation info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => Session::ID_TYPE_EMAIL_ACTIVATE,
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);
    }

    public function testEmailActivateShow(): void
    {
        $this->insertIdentityEmailActivate();

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo(EmailActivator::class))
            ->get('/auth/a/show');

        $result->assertStatus(200);

        // Should have sent an email with the link....
        $this->assertStringContainsString(
            'Please use the code below to activate your account and start using the site',
            service('email')->archive['body']
        );
        $this->assertMatchesRegularExpression(
            '!<h1>[0-9]{6}</h1>!',
            service('email')->archive['body']
        );
    }

    public function testEmailActivateVerify(): void
    {
        $this->insertIdentityEmailActivate();

        $this->user->active = false;
        $model              = auth()->getProvider();
        $model->save($this->user);

        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo(EmailActivator::class))
            ->post('/auth/a/verify', [
                'token' => '123456',
            ]);

        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Identity should have been removed
        $this->dontSeeInDatabase($this->tables['identities'], [
            'user_id' => $this->user->id,
            'type'    => Session::ID_TYPE_EMAIL_2FA,
        ]);

        // Session should have been cleared
        $result->assertSessionMissing('auth_action');

        // User should have been set as active
        $this->seeInDatabase($this->tables['users'], [
            'id'     => $this->user->id,
            'active' => 1,
        ]);
    }

    public function testEmailActivateCannotBeBypassed(): void
    {
        // Ensure filter is enabled for all routes
        $config                      = config('Filters');
        $config->globals['before'][] = 'session';
        Factories::injectMock('config', 'Filters', $config);

        $this->insertIdentityEmailActivate();

        // Try to visit any other page, skipping the 2FA
        $result = $this->actingAs($this->user, true)
            ->withSession($this->getSessionUserInfo(EmailActivator::class))
            ->get('/');

        $result->assertRedirect();
        $this->assertSame(site_url('/auth/a/show'), $result->getRedirectUrl());
    }
}
