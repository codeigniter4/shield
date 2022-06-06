<?php

namespace Tests\Collectors;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Collectors\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class AuthTest extends TestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    protected $refresh = true;
    private User $user;
    private Auth $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = fake(UserModel::class, ['username' => 'John Smith']);

        $this->collector = new Auth();
    }

    public function testDisplayNotLoggedIn(): void
    {
        $output = $this->collector->display();

        $this->assertStringContainsString('Not logged in', $output);
    }

    public function testDisplayLoggedIn(): void
    {
        $authenticator = service('auth')->getAuthenticator();
        assert($authenticator instanceof Session);
        $authenticator->login($this->user);
        $this->user->addGroup('admin', 'beta');

        $output = $this->collector->display();

        $this->assertStringContainsString('Current Use', $output);
        $this->assertStringContainsString('<td>Username</td><td>John Smith</td>', $output);
        $this->assertStringContainsString('<td>Groups</td><td>admin, beta</td>', $output);
    }

    public function testDisplayNotLoggedInAfterLogout(): void
    {
        $authenticator = service('auth')->getAuthenticator();
        assert($authenticator instanceof Session);
        $authenticator->login($this->user);

        $authenticator->logout();

        $output = $this->collector->display();
        $this->assertStringContainsString('Not logged in', $output);
    }

    public function testGetTitleDetails(): void
    {
        $output = $this->collector->getTitleDetails();

        $this->assertStringContainsString(Session::class, $output);
    }

    public function testGetBadgeValueReturnsUserId(): void
    {
        /** @var Session $authenticator */
        $authenticator = service('auth')->getAuthenticator();
        $authenticator->login($this->user);

        $output = (string) $this->collector->getBadgeValue();

        $this->assertStringContainsString('1', $output);
    }
}
