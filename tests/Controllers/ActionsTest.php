<?php

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Test\AuthenticationTesting;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class ActionsTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;
    use FakeUser;

    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        helper('auth');

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

    public function testActionShowNoneAvailable()
    {
        $this->expectException(PageNotFoundException::class);

        $result = $this->withSession([])->get('/auth/a/show');

        // Nothing found, it should die gracefully.
        $result->assertStatus(404);
    }

    public function testEmail2FAShow()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => Email2FA::class,
            ])->get('/auth/a/show');

        $result->assertStatus(200);
        // Should autopopulate in the form
        $result->assertSee($this->user->email);
    }

    public function testEmail2FAHandleInvalidEmail()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => Email2FA::class,
            ])->post('/auth/a/handle', [
                'email' => 'foo@example.com',
            ]);

        $result->assertRedirect();
        $result->assertEquals(site_url('/auth/a/show'), $result->getRedirectUrl());
        $result->assertSessionHas('error', lang('Auth.invalidEmail'));
    }

    public function testEmail2FAHandleSendsEmail()
    {
        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
            'secret'  => '123456',
            'name'    => 'login',
            'extra'   => lang('Auth.need2FA'),
        ]);

        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => Email2FA::class,
            ])->post('/auth/a/handle', [
                'email' => $this->user->email,
            ]);

        $result->assertStatus(200);
        $result->assertSee(lang('Auth.emailEnterCode'));

        // Should have sent an email with the code....
        $this->assertStringContainsString('Your authentication token is:', service('email')->archive['body']);
    }

    public function testEmail2FAVerifyFails()
    {
        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
            'secret'  => '123456',
            'name'    => 'login',
            'extra'   => lang('Auth.need2FA'),
        ]);

        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => Email2FA::class,
            ])->post('/auth/a/verify', [
                'token' => '234567',
            ]);

        $result->assertStatus(200);
        $result->assertSee(lang('Auth.invalid2FAToken'));
    }

    public function testEmail2FAVerify()
    {
        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
            'secret'  => '123456',
            'name'    => 'login',
            'extra'   => lang('Auth.need2FA'),
        ]);

        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => Email2FA::class,
            ])->post('/auth/a/verify', [
                'token' => '123456',
            ]);

        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Identity should have been removed
        $this->dontSeeInDatabase('auth_identities', [
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
        ]);

        // Session should have been cleared
        $result->assertSessionMissing('auth_action');
    }

    public function testShowEmail2FACreatesIdentity()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => Email2FA::class,
            ])
            ->get('/auth/a/show');

        $result->assertOK();

        $this->seeInDatabase('auth_identities', [
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
            'name'    => 'login',
        ]);
    }

    public function testEmail2FACannotBeBypassed()
    {
        // Ensure filter is enabled for all routes
        $config                      = config('Filters');
        $config->globals['before'][] = 'session';
        Factories::injectMock('config', 'Filters', $config);

        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
            'secret'  => '123456',
            'name'    => 'login',
            'extra'   => lang('Auth.need2FA'),
        ]);

        // Try to visit any other page, skipping the 2FA
        $result = $this->actingAs($this->user)
            ->get('/');

        $result->assertRedirect();
        $this->assertSame(site_url('/auth/a/show'), $result->getRedirectUrl());
    }

    public function testEmailActivateShow()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => EmailActivator::class,
            ])->get('/auth/a/show');

        $result->assertStatus(200);

        // Should have sent an email with the link....
        $this->assertStringContainsString(
            'Please use the code below to activate your account and start using the site',
            service('email')->archive['body']
        );
        $this->assertMatchesRegularExpression(
            '!<p>[0-9]{6}</p>!',
            service('email')->archive['body']
        );
    }

    public function testEmailActivateVerify()
    {
        // An identity with Email activation info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_activate',
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);

        $this->user->active = false;
        $model              = auth()->getProvider();
        $model->save($this->user);

        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => EmailActivator::class,
            ])->post('/auth/a/verify', [
                'token' => '123456',
            ]);

        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Identity should have been removed
        $this->dontSeeInDatabase('auth_identities', [
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
        ]);

        // Session should have been cleared
        $result->assertSessionMissing('auth_action');

        // User should have been set as active
        $this->seeInDatabase('users', [
            'id'     => $this->user->id,
            'active' => 1,
        ]);
    }

    public function testEmailActivateCannotBeBypassed()
    {
        // Ensure filter is enabled for all routes
        $config                      = config('Filters');
        $config->globals['before'][] = 'session';
        Factories::injectMock('config', 'Filters', $config);

        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_activate',
            'secret'  => '123456',
            'name'    => 'register',
            'extra'   => lang('Auth.needVerification'),
        ]);

        // Try to visit any other page, skipping the 2FA
        $result = $this->actingAs($this->user)
            ->get('/');

        $result->assertRedirect();
        $this->assertSame(site_url('/auth/a/show'), $result->getRedirectUrl());
    }
}
