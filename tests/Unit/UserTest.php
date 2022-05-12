<?php

namespace Tests\Unit;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\DatabaseTestTrait;
use Sparks\Shield\Entities\Login;
use Sparks\Shield\Entities\UserIdentity;
use Sparks\Shield\Models\LoginModel;
use Sparks\Shield\Models\UserIdentityModel;
use Sparks\Shield\Models\UserModel;
use Tests\Support\FakeUser;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class UserTest extends TestCase
{
    use DatabaseTestTrait;
    use FakeUser;

    protected $namespace;
    protected $refresh = true;

    public function testGetIdentitiesNone()
    {
        // when none, returns empty array
        $this->assertEmpty($this->user->identities);
    }

    public function testGetIdentitiesSome()
    {
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        $identities = $this->user->identities;

        $this->assertCount(2, $identities);
    }

    public function testGetIdentitiesByType()
    {
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        $identities = $this->user->identitiesOfType('access_token');
        $this->assertCount(1, $identities);
        $this->assertInstanceOf(UserIdentity::class, $identities[0]);
        $this->assertSame('access_token', $identities[0]->type);

        $this->assertEmpty($this->user->identitiesOfType('foo'));
    }

    public function testModelWithIdentities()
    {
        fake(UserModel::class);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        // Grab the user again, using the model's identity helper
        model(UserModel::class)->withIdentities()->findAll(); // @phpstan-ignore-line

        $identities = $this->user->identities;

        $this->assertCount(2, $identities);
    }

    public function testLastLogin()
    {
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'email_password', 'secret' => 'foo@example.com']);

        // No logins found.
        $this->assertNull($this->user->lastLogin());

        $login = fake(LoginModel::class, ['email' => $this->user->email, 'user_id' => $this->user->id]);
        fake(LoginModel::class, ['email' => $this->user->email, 'user_id' => $this->user->id, 'success' => false]);

        $last = $this->user->lastLogin();

        $this->assertInstanceOf(Login::class, $last);
        $this->assertSame($login->id, $last->id);
        $this->assertInstanceOf(Time::class, $last->date);
    }
}
