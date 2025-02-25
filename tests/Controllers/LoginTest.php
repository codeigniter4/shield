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
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Actions\Email2FA;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Config\Validation;
use Tests\Support\DatabaseTestCase;
use Tests\Support\FakeAction;
use Tests\Support\FakeUser;

/**
 * @internal
 */
final class LoginTest extends DatabaseTestCase
{
    use FeatureTestTrait;
    use FakeUser;

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->seeInDatabase($this->tables['logins'], [
            'identifier' => 'fooled@example.com',
            'user_id'    => null,
            'success'    => 0,
        ]);

        $this->assertNotEmpty(session('error'));
        $this->assertSame(lang('Auth.badAttempt'), session('error'));
    }

    public function testLoginTooLongPasswordDefault(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'fooled@example.com',
            'password' => str_repeat('a', 73),
        ]);

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionMissing('error');
        $result->assertSessionHas(
            'errors',
            ['password' => 'Password cannot exceed 72 bytes in length.'],
        );
    }

    public function testLoginTooLongPasswordArgon2id(): void
    {
        /** @var Auth $config */
        $config                = config('Auth');
        $config->hashAlgorithm = PASSWORD_ARGON2ID;

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'fooled@example.com',
            'password' => str_repeat('a', 256),
        ]);

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionMissing('error');
        $result->assertSessionHas(
            'errors',
            ['password' => 'The Password field cannot exceed 255 characters in length.'],
        );
    }

    public function testLoginActionEmailSuccess(): void
    {
        Time::setTestNow('March 10, 2017', 'UTC');

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result->assertSessionHas('user', ['id' => 1]);
        $result->assertStatus(302);
        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Login should have been recorded successfully
        $this->seeInDatabase($this->tables['logins'], [
            'identifier' => 'foo@example.com',
            'user_id'    => $this->user->id,
            'success'    => 1,
        ]);
        // Last Used date should have been set
        $identity = $this->user->getEmailIdentity();
        $this->assertInstanceOf(Time::class, $identity->last_used_at);
        $this->assertSame(Time::now()->getTimestamp(), $identity->last_used_at->getTimestamp());

        // Session should have `logged_in` value with user's id
        $this->assertSame($this->user->id, session('user')['id']);

        Time::setTestNow();
    }

    public function testAfterLoggedInNotDisplayLoginPage(): void
    {
        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->get('/login');
        $result->assertRedirectTo(config('Auth')->loginRedirect());
    }

    public function testLoginActionUsernameSuccess(): void
    {
        Time::setTestNow('March 10, 2017', 'UTC');

        // Add 'username' to $validFields
        $authConfig                = config('Auth');
        $authConfig->validFields[] = 'username';
        Factories::injectMock('config', 'Auth', $authConfig);

        // Change the validation rules
        $config = new class () extends Validation {
            public $login = [
                'password' => 'required',
            ];
        };
        $config->login['username'] = config('Auth')->usernameValidationRules;
        Factories::injectMock('config', 'Validation', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        $result = $this->post('/login', [
            'username' => $this->user->username,
            'password' => 'secret123',
        ]);

        $result->assertSessionHas('user', ['id' => 1]);
        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url(), $result->getRedirectUrl());

        // Login should have been recorded successfully
        $this->seeInDatabase($this->tables['logins'], [
            'identifier' => $this->user->username,
            'user_id'    => $this->user->id,
            'success'    => 1,
        ]);
        // Last Used date should have been set
        $identity = $this->user->getEmailIdentity();
        $this->assertInstanceOf(Time::class, $identity->last_used_at);
        $this->assertSame(Time::now()->getTimestamp(), $identity->last_used_at->getTimestamp());

        // Session should have `logged_in` value with user's id
        $this->assertSame($this->user->id, session('user')['id']);

        Time::setTestNow();
    }

    public function testLogoutAction(): void
    {
        // log them in
        session()->set('user', ['id' => $this->user->id]);

        $result = $this->get('logout');

        $result->assertStatus(302);
        $result->assertSessionHas('message', lang('Auth.successLogout'));
        $this->assertSame(site_url(config('Auth')->redirects['logout']), $result->getRedirectUrl());

        $this->assertNull(session('user'));
    }

    public function testLoginRedirectsToActionIfDefined(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->Mfa                 = true;
        $config->actionsMfa['email'] = Email2FA::class;
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
        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url('auth/a/show'), $result->getRedirectUrl());
    }

    public function testLoginNotForcedMFAUserWithoutMFA(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->Mfa                 = true;
        $config->forceMfa            = false;
        $config->actionsMfa['email'] = Email2FA::class;
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
        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url(), $result->getRedirectUrl());
    }

    public function testLoginNotForcedMFAUserWithMFA(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->Mfa                 = true;
        $config->forceMfa            = false;
        $config->actionsMfa['email'] = Email2FA::class;
        Factories::injectMock('config', 'Auth', $config);

        $users = model(UserModel::class);
        $this->assertFalse($this->user->isMfaActive());
        $this->user->fill(['mfa' => 1]);
        $users->save($this->user);
        $this->assertTrue($this->user->isMfaActive());
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
        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url('auth/a/show'), $result->getRedirectUrl());
    }

    public function testLoginForcedMFAUserWithoutMFA(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->Mfa                 = true;
        $config->forceMfa            = true;
        $config->actionsMfa['email'] = Email2FA::class;
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
        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url('auth/a/show'), $result->getRedirectUrl());
    }

    public function testLoginMFADisabled(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->actionsMfa['email'] = Email2FA::class;
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
        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url(), $result->getRedirectUrl());
    }

    public function testLoginMultipleMFA(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->Mfa                 = true;
        $config->actionsMfa['email'] = Email2FA::class;
        $config->actionsMfa['test']  = FakeAction::class;
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'test', 'name' => 'login', 'secret' => 'secret-for-test-token', 'extra' => serialize(['recover_pos' => []])]);

        $this->assertFalse($this->user->isMfaActive());
        $this->user->fill(['mfa' => 1]);
        $users = model(UserModel::class);
        $users->save($this->user);
        $this->assertTrue($this->user->isMfaActive());

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        // Should have been redirected to the action's page.
        $result->assertStatus(302);
        $result->assertRedirect();

        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url('auth/a/show'), $result->getRedirectUrl());
    }

    public function testLoginMultipleMFAGroups(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->Mfa                 = true;
        $config->forceMfa            = false;
        $config->actionsMfa['email'] = Email2FA::class;
        $config->actionsMfa['test']  = FakeAction::class;
        $config->matrixMfa['admin']  = 'test';
        Factories::injectMock('config', 'Auth', $config);

        $this->user->createEmailIdentity([
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'test', 'name' => 'login', 'secret' => 'secret-for-test-token', 'extra' => '']);

        $this->assertFalse($this->user->isMfaActive());
        $this->user->fill(['mfa' => 1]);
        $users = model(UserModel::class);
        $users->save($this->user);
        $this->assertTrue($this->user->isMfaActive());

        $result = $this->post('/login', [
            'email'    => 'foo@example.com',
            'password' => 'secret123',
        ]);

        // Should have been redirected to the action's page.
        $result->assertStatus(302);
        $result->assertRedirect();

        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');
        $this->assertSame(site_url('auth/a/show'), $result->getRedirectUrl());
    }
}
