<?php

declare(strict_types=1);

namespace Tests\Authentication;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
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
        $routeCollection = service('routes');
        auth()->routes($routeCollection);

        Services::injectMock('routes', $routeCollection);
    }

    public function testCanSeeMagicLinkForm(): void
    {
        $result = $this->get(route_to('magic-link'));

        $result->assertOK();
        $result->assertSee(lang('Auth.useMagicLink'));
    }

    public function testMagicLinkSubmitNoEmail(): void
    {
        $result = $this->post(route_to('magic-link'), [
            'email' => '',
        ]);

        $result->assertRedirectTo(route_to('magic-link'));
        $expected = ['email' => 'The Email Address field is required.'];

        $result->assertSessionHas('errors', $expected);
    }

    public function testMagicLinkSubmitBadEmail(): void
    {
        $result = $this->post(route_to('magic-link'), [
            'email' => 'foo@example.com',
        ]);

        $result->assertRedirectTo(route_to('magic-link'));
        $result->assertSessionHas('error', lang('Auth.invalidEmail'));
    }

    public function testMagicLinkSubmitSuccess(): void
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
            'type'    => Session::ID_TYPE_MAGIC_LINK,
        ]);
    }

    public function testMagicLinkVerifyNoToken(): void
    {
        $result = $this->get(route_to('verify-magic-link'));

        $result->assertRedirectTo(route_to('magic-link'));
        $result->assertSessionHas('error', lang('Auth.magicTokenNotFound'));
    }

    public function testMagicLinkVerifyExpired(): void
    {
        $identities = new UserIdentityModel();
        /**
         * @phpstan-var User
         */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => 'abasdasdf',
            'expires' => Time::now()->subDays(5),
        ]);

        $result = $this->get(route_to('verify-magic-link') . '?token=abasdasdf');

        $result->assertRedirectTo(route_to('magic-link'));
        $result->assertSessionHas('error', lang('Auth.magicLinkExpired'));

        // It should have set temp session var
        $this->assertFalse(session()->has('magicLogin'));
    }

    public function testMagicLinkVerifySuccess(): void
    {
        $identities = new UserIdentityModel();
        /** @var User $user */
        $user = fake(UserModel::class);
        $user->createEmailIdentity(['email' => 'foo@example.com', 'password' => 'secret123']);
        $identities->insert([
            'user_id' => $user->id,
            'type'    => Session::ID_TYPE_MAGIC_LINK,
            'secret'  => 'abasdasdf',
            'expires' => Time::now()->addMinutes(60),
        ]);

        $result = $this->get(route_to('verify-magic-link') . '?token=abasdasdf');

        $result->assertRedirectTo(site_url());
        $result->assertSessionHas('user', ['id' => $user->id]);
        $this->assertTrue(auth()->loggedIn());

        // It should have set temp session var
        $this->assertTrue(session()->has('magicLogin'));
        $this->assertTrue(session('magicLogin'));
    }
}
