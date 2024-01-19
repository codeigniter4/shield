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
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
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
            'expires' => Time::now()->addMinutes(60)->format('Y-m-d H:i:s'),
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
            ]
        );
        $this->assertFalse(auth()->loggedIn());
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
}
