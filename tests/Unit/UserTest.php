<?php

use CodeIgniter\Test\DatabaseTestTrait;
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

    protected $namespace = '\Sparks\Shield';
    protected $refresh   = true;

    public function testGetIdentitiesNone()
    {
        // when none, returns empty array
        $this->assertEmpty($this->user->identities);
    }

    public function testGetIdentitiesSome()
    {
        $passIdentity  = fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        $tokenIdentity = fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        $identities = $this->user->identities;
        $this->assertCount(2, $identities);
    }

    public function testGetIdentitiesByType()
    {
        $passIdentity  = fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        $tokenIdentity = fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        $identities = $this->user->identitiesOfType('access_token');
        $this->assertCount(1, $identities);
        $this->assertInstanceOf(UserIdentity::class, $identities[0]);
        $this->assertSame('access_token', $identities[0]->type);

        $this->assertEmpty($this->user->identitiesOfType('foo'));
    }

    public function testModelWithIdentities()
    {
        $user2 = fake(UserModel::class);

        $passIdentity  = fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        $tokenIdentity = fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        // Grab the user again, using the model's identity helper
        $users = model(UserModel::class)->withIdentities()->findAll(); // @phpstan-ignore-line

        $identities = $this->user->identities;
        $this->assertCount(2, $identities);
    }

    public function testLastLogin()
    {
        $emailIdentity = fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'email_password', 'secret' => 'foo@example.com']);

        // No logins found.
        $this->assertNull($this->user->lastLogin());

        $login    = fake(LoginModel::class, ['email' => $this->user->email, 'user_id' => $this->user->id]);
        $badLogin = fake(LoginModel::class, ['email' => $this->user->email, 'user_id' => $this->user->id, 'success' => false]);

        $last = $this->user->lastLogin();
        $this->assertInstanceOf(\Sparks\Shield\Entities\Login::class, $last);
        $this->assertSame($login->id, $last->id);
        $this->assertInstanceOf(\CodeIgniter\I18n\Time::class, $last->date);
    }
}
