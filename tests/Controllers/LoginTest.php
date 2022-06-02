<?php

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class LoginTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use FakeUser;

    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        helper('auth');

        // Add auth routes
        $routes = service('routes');
        auth()->routes($routes);
        Services::injectMock('routes', $routes);
    }

    public function testLoginBadEmail(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'fooled@example.com',
            'password' => 'secret123',
        ]);

        $result->assertStatus(302);
        $result->assertRedirect();
        $this->assertSame(site_url('/login'), $result->getRedirectUrl());

        // Login should have been recorded successfully
        $this->seeInDatabase('auth_logins', [
            'identifier' => 'fooled@example.com',
            'user_id'    => null,
            'success'    => 0,
        ]);

        $this->assertNotEmpty(session('error'));
        $this->assertSame(lang('Auth.badAttempt'), session('error'));
    }

    public function testLoginActionEmailSuccess(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result->assertStatus(302);
        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Login should have been recorded successfully
        $this->seeInDatabase('auth_logins', [
            'identifier' => 'foo@example.com',
            'user_id'    => $this->user->id,
            'success'    => true,
        ]);
        // Last Used date should have been set
        $identity = $this->user->getEmailIdentity();
        $this->assertCloseEnough($identity->last_used_at->getTimestamp(), Time::now()->getTimestamp());

        // Session should have `logged_in` value with user's id
        $this->assertSame($this->user->id, session('user')['id']);
    }

    public function testLoginActionUsernameSuccess(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'username' => $this->user->username,
            'password' => 'secret123',
        ]);

        $result->assertStatus(302);
        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Login should have been recorded successfully
        $this->seeInDatabase('auth_logins', [
            'identifier' => $this->user->username,
            'user_id'    => $this->user->id,
            'success'    => true,
        ]);
        // Last Used date should have been set
        $identity = $this->user->getEmailIdentity();
        $this->assertCloseEnough($identity->last_used_at->getTimestamp(), Time::now()->getTimestamp());

        // Session should have `logged_in` value with user's id
        $this->assertSame($this->user->id, session('user')['id']);
    }

    public function testLogoutAction(): void
    {
        // log them in
        session()->set('user', ['id' => $this->user->id]);

        $result = $this->get('logout');

        $result->assertStatus(302);
        $this->assertSame(site_url(config('Auth')->redirects['logout']), $result->getRedirectUrl());

        $this->assertNull(session('user'));
    }

    public function testLoginRedirectsToActionIfDefined(): void
    {
        // Ensure our action is defined
        $config                   = config('Auth');
        $config->actions['login'] = Email2FA::class;
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        // Should have been redirected to the action's page.
        $result->assertStatus(302);
        $result->assertRedirect();
        $this->assertSame(site_url('auth/a/show'), $result->getRedirectUrl());
    }
}
