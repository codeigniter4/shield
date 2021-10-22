<?php

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\FeatureTestTrait;
use Sparks\Shield\Authentication\Passwords\ValidationRules;

/**
 * @internal
 */
final class RegisterTest extends \CodeIgniter\Test\CIDatabaseTestCase
{
    use FeatureTestTrait;

    protected $namespace;

    protected function setUp(): void
    {
        \Config\Services::reset();

        parent::setUp();

        helper('auth');
        \CodeIgniter\Config\Factories::reset();

        // Add auth routes
        $routes = service('routes');
        auth()->routes($routes);
        \Config\Services::injectMock('routes', $routes);

        // Ensure password validation rule is present
        $config             = config('Validation');
        $config->ruleSets[] = ValidationRules::class;
        $config->ruleSets   = array_reverse($config->ruleSets);
        \CodeIgniter\Config\Factories::injectMock('config', 'Validation', $config);
    }

    public function testRegisterActionSuccess()
    {
        $result = $this->withSession()->post('/register', [
            'username'     => 'JohnDoe',
            'email'        => 'john.doe@example.com',
            'password'     => 'secret things might happen here',
            'pass_confirm' => 'secret things might happen here',
        ]);

        $result->assertStatus(302);
        $result->assertRedirect();
        $this->assertSame(site_url(), $result->getRedirectUrl());
        // User saved to DB
        $this->seeInDatabase('users', [
            'username' => 'JohnDoe',
        ]);
        // User has email/password identity
        $user = model('UserModel')->where('username', 'JohnDoe')->first();
        $this->seeInDatabase('auth_identities', [
            'user_id' => $user->id,
            'type'    => 'email_password',
            'secret'  => 'john.doe@example.com',
        ]);
        // User added to default group
        $this->assertTrue($user->inGroup(config('AuthGroups')->defaultGroup));
        $this->assertFalse($user->inGroup('admin'));
    }

    public function testRegisterDisplaysForm()
    {
        $result = $this->withSession()->get('/register');

        $result->assertStatus(200);
        $result->assertSee(lang('Auth.register'));
    }

    public function testRegisterRedirectsIfNotAllowed()
    {
        $config                    = config('Auth');
        $config->allowRegistration = false;
        CodeIgniter\Config\Config::injectMock('Auth', $config);

        $result = $this->withSession()->get('/register');

        $result->assertStatus(302);
        $result->assertRedirect();
    }

    public function testRegisterActionRedirectsIfNotAllowed()
    {
        $config                    = config('Auth');
        $config->allowRegistration = false;
        CodeIgniter\Config\Config::injectMock('Auth', $config);

        $result = $this->withSession()->post('/register');

        $result->assertStatus(302);
        $result->assertRedirect();
    }

    public function testRegisterActionInvalidData()
    {
        $result = $this->withSession()->post('/register');

        $result->assertStatus(302);
        $this->assertCount(4, session('errors'));
    }

    public function testRegisterRedirectsToActionIfDefined()
    {
        // Ensure our action is defined
        $config                      = config('Auth');
        $config->actions['register'] = \Sparks\Shield\Authentication\Actions\EmailActivator::class;
        Factories::injectMock('config', 'Auth', $config);

        $result = $this->post('/register', [
            'email'        => 'foo@example.com',
            'username'     => 'foo',
            'password'     => 'abkdhflkjsdflkjasd;lkjf',
            'pass_confirm' => 'abkdhflkjsdflkjasd;lkjf',
        ]);

        // Should have been redirected to the action's page.
        $result->assertRedirectTo('/auth/a/show');
    }

    protected function setupConfig()
    {
        $config             = config('Validation');
        $config->ruleSets[] = ValidationRules::class;
        \CodeIgniter\Config\Factories::injectMock('config', 'Validation', $config);
    }
}
