<?php

namespace Tests\Controllers;

use CodeIgniter\Config\Factories;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Authentication\Passwords\ValidationRules;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class RegisterTest extends DatabaseTestCase
{
    use FeatureTestTrait;

    protected $namespace;

    protected function setUp(): void
    {
        Services::reset(true);

        parent::setUp();

        helper('auth');
        Factories::reset();

        // Add auth routes
        $routes = service('routes');
        auth()->routes($routes);
        Services::injectMock('routes', $routes);

        // Ensure password validation rule is present
        $config             = config('Validation');
        $config->ruleSets[] = ValidationRules::class;
        $config->ruleSets   = array_reverse($config->ruleSets);
        Factories::injectMock('config', 'Validation', $config);
    }

    public function testRegisterActionSuccess(): void
    {
        $result = $this->withSession()->post('/register', [
            'username'         => 'JohnDoe',
            'email'            => 'john.doe@example.com',
            'password'         => 'secret things might happen here',
            'password_confirm' => 'secret things might happen here',
        ]);

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionMissing('error');
        $result->assertSessionMissing('errors');

        $this->assertSame(site_url(), $result->getRedirectUrl());

        // User saved to DB
        $this->seeInDatabase('users', [
            'username' => 'JohnDoe',
        ]);

        // User has email/password identity
        /** @var User $user */
        $user = model(UserModel::class)->where('username', 'JohnDoe')->first();

        $this->seeInDatabase('auth_identities', [
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
            'secret'  => 'john.doe@example.com',
        ]);

        // User added to default group
        $this->assertTrue($user->inGroup(config('AuthGroups')->defaultGroup));
        $this->assertFalse($user->inGroup('admin'));

        $this->assertTrue($user->active);
    }

    public function testRegisterDisplaysForm(): void
    {
        $result = $this->withSession()->get('/register');

        $result->assertStatus(200);
        $result->assertSee(lang('Auth.register'));
    }

    public function testRegisterRedirectsIfNotAllowed(): void
    {
        $config                    = config('Auth');
        $config->allowRegistration = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->get('/register');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas('error');
    }

    public function testRegisterActionRedirectsIfNotAllowed(): void
    {
        $config                    = config('Auth');
        $config->allowRegistration = false;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->withSession()->post('/register');

        $result->assertStatus(302);
        $result->assertRedirect();
        $result->assertSessionHas('error');
    }

    public function testRegisterActionInvalidData(): void
    {
        $result = $this->withSession()->post('/register');

        $result->assertStatus(302);
        $this->assertCount(4, session('errors'));
    }

    public function testRegisterRedirectsToActionIfDefined(): void
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->actions['register'] = EmailActivator::class;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->post('/register', [
            'email'            => 'foo@example.com',
            'username'         => 'foo',
            'password'         => 'abkdhflkjsdflkjasd;lkjf',
            'password_confirm' => 'abkdhflkjsdflkjasd;lkjf',
        ]);

        // Should have been redirected to the action's page.
        $result->assertRedirectTo('/auth/a/show');

        // Should NOT have activated the user
        $this->seeInDatabase('users', [
            'username' => 'foo',
            'active'   => 0,
        ]);
    }

    protected function setupConfig(): void
    {
        $config             = config('Validation');
        $config->ruleSets[] = ValidationRules::class;
        Factories::injectMock('config', 'Validation', $config);
    }
}
