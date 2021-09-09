<?php

use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Sparks\Shield\Models\UserIdentityModel;
use Sparks\Shield\Test\AuthenticationTesting;
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

    protected $namespace = '\Sparks\Shield';

    protected function setUp(): void
    {
        parent::setUp();

        helper('auth');

        // Ensure our actions are registered with the system
        $config                   = config('Auth');
        $config->actions['login'] = \Sparks\Shield\Authentication\Actions\Email2FA::class;
        \CodeIgniter\Config\Factories::injectMock('config', 'Auth', $config);

        // Add auth routes
        $routes = service('routes');
        auth()->routes($routes);
        \Config\Services::injectMock('routes', $routes);

        $_SESSION = [];

        $this->user->createEmailIdentity(['email' => 'johnsmith@example.com', 'password' => 'secret123']);
    }

    public function testActionShowNoneAvailable()
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);

        $result = $this->withSession([])->get('/auth/a/show');

        // Nothing found, it should die gracefully.
        $result->assertStatus(404);
    }

    public function testEmail2FAShow()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => \Sparks\Shield\Authentication\Actions\Email2FA::class,
            ])->get('/auth/a/show');

        $result->assertStatus(200);
        // Should autopopulate in the form
        $result->assertSee($this->user->email);
    }

    public function testEmail2FAHandleInvalidEmail()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => \Sparks\Shield\Authentication\Actions\Email2FA::class,
            ])->post('/auth/a/handle', [
                'email' => 'foo@example.com',
            ]);

        $result->assertRedirect();
        $result->assertEquals(site_url('/auth/a/show'), $result->getRedirectUrl());
        $result->assertSessionHas('error', lang('Auth.invalidEmail'));
    }

    public function testEmail2FAHandleSendsEmail()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => \Sparks\Shield\Authentication\Actions\Email2FA::class,
            ])->post('/auth/a/handle', [
                'email' => $this->user->email,
            ]);

        $result->assertStatus(200);
        $result->assertSee(lang('Auth.emailEnterCode'));

        // Should have saved an identity with a code
        $this->seeInDatabase('auth_identities', [
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
        ]);

        // Should have sent an email with the code....
        $this->assertTrue(strpos(service('email')->archive['body'], 'Your authentication token is:') !== false);
    }

    public function testEmail2FAVerifyFails()
    {
        // An identity with 2FA info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_2fa',
            'secret'  => '123456',
        ]);

        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => \Sparks\Shield\Authentication\Actions\Email2FA::class,
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
        ]);

        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => \Sparks\Shield\Authentication\Actions\Email2FA::class,
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

    public function testEmailActivateShow()
    {
        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => \Sparks\Shield\Authentication\Actions\EmailActivator::class,
            ])->get('/auth/a/show');

        $result->assertStatus(200);

        // Should have sent an email with the link....
        $this->assertTrue(strpos(service('email')->archive['body'], 'Please click the link below to activate your account') !== false);
        $this->assertTrue(strpos(service('email')->archive['body'], '/auth/a/verify?c=') !== false);
    }

    public function testEmailActivateVerify()
    {
        // An identity with Email activation info would have been stored previously
        $identities = model(UserIdentityModel::class);
        $identities->insert([
            'user_id' => $this->user->id,
            'type'    => 'email_activate',
            'secret'  => '123456',
        ]);

        $result = $this->actingAs($this->user)
            ->withSession([
                'auth_action' => \Sparks\Shield\Authentication\Actions\EmailActivator::class,
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
}
