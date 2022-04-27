<?php

namespace Tests\Authentication;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class MagicLinkTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh = true;
    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        // Load our Auth routes in the collection
        helper('auth');
        $routeCollection = service('routes');
        auth()->routes($routeCollection);

        Services::injectMock('routes', $routeCollection);
    }

    public function testCanSeeMagicLinkForm()
    {
        $result = $this->get(route_to('magic-link'));

        $result->assertOK();
        $result->assertSee(lang('Auth.useMagicLink'));
    }

    public function testMagicLinkSubmitNoEmail()
    {
        $result = $this->post(route_to('magic-link'), [
            'email' => '',
        ]);

        $result->assertRedirectTo(route_to('magic-link'));
        $result->assertSessionHas('error', lang('Auth.invalidEmail'));
    }

    public function testMagicLinkSubmitBadEmail()
    {
        $result = $this->post(route_to('magic-link'), [
            'email' => 'foo@example.com',
        ]);

        $result->assertRedirectTo(route_to('magic-link'));
        $result->assertSessionHas('error', lang('Auth.invalidEmail'));
    }

    public function testMagicLinkSubmitSuccess()
    {
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);

        $result = $this->post(route_to('magic-link'), [
            'email' => 'foo@example.com',
        ]);

        $result->assertOK();
        $result->assertSee(lang('Auth.checkYourEmail'));

        $this->seeInDatabase('auth_identities', [
            'user_id' => $user->id,
            'type'    => 'magic-link',
        ]);
    }

    public function testMagicLinkVerifyNoToken()
    {
        $result = $this->get(route_to('verify-magic-link'));

        $result->assertRedirectTo(route_to('magic-link'));
        $result->assertSessionHas('error', lang('Auth.magicTokenNotFound'));
    }

    public function testMagicLinkVerifyExpired()
    {
        $identities = new UserIdentityModel();
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $identities->insert([
            'user_id' => $user->id,
            'type'    => 'magic-link',
            'secret'  => 'abasdasdf',
            'expires' => Time::now()->subDays(5),
        ]);

        $result = $this->get(route_to('verify-magic-link') . '?token=' . 'abasdasdf');

        $result->assertRedirectTo(route_to('magic-link'));
        $result->assertSessionHas('error', lang('Auth.magicLinkExpired'));
    }

    public function testMagicLinkVerifySuccess()
    {
        $identities = new UserIdentityModel();
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $identities->insert([
            'user_id' => $user->id,
            'type'    => 'magic-link',
            'secret'  => 'abasdasdf',
            'expires' => Time::now()->addMinutes(60),
        ]);

        $result = $this->get(route_to('verify-magic-link') . '?token=' . 'abasdasdf');

        $result->assertRedirectTo(site_url());
        $result->assertSessionHas('logged_in', $user->id);
        $this->assertTrue(auth()->loggedIn());
    }
}
