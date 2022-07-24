<?php

namespace Tests\Unit;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Entities\Login;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
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

    public function testGetIdentitiesNone(): void
    {
        // when none, returns empty array
        $this->assertEmpty($this->user->identities);
    }

    public function testGetIdentitiesSome(): void
    {
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        $identities = $this->user->identities;

        $this->assertCount(2, $identities);
    }

    public function testGetIdentitiesByType(): void
    {
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        $identities = $this->user->getIdentities('access_token');

        $this->assertCount(1, $identities);
        $this->assertInstanceOf(UserIdentity::class, $identities[0]);
        $this->assertSame('access_token', $identities[0]->type);
        $this->assertEmpty($this->user->getIdentities('foo'));
    }

    public function testModelfindAllWithIdentities(): void
    {
        fake(UserModel::class);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        // Grab the user again, using the model's identity helper
        model(UserModel::class)->withIdentities()->findAll();

        $identities = $this->user->identities;

        $this->assertCount(2, $identities);
    }

    public function testModelfindByIdWithIdentities(): void
    {
        fake(UserModel::class);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'password']);
        fake(UserIdentityModel::class, ['user_id' => $this->user->id, 'type' => 'access_token']);

        // Grab the user again, using the model's identity helper
        $user = model(UserModel::class)->withIdentities()->findById(1);

        $this->assertCount(2, $user->identities);
    }

    public function testLastLogin(): void
    {
        fake(
            UserIdentityModel::class,
            ['user_id' => $this->user->id, 'type' => Session::ID_TYPE_EMAIL_PASSWORD, 'secret' => 'foo@example.com']
        );

        // No logins found.
        $this->assertNull($this->user->lastLogin());

        fake(
            LoginModel::class,
            ['id_type' => 'email', 'identifier' => $this->user->email, 'user_id' => $this->user->id]
        );
        $login2 = fake(
            LoginModel::class,
            ['id_type' => 'email', 'identifier' => $this->user->email, 'user_id' => $this->user->id]
        );
        fake(
            LoginModel::class,
            [
                'id_type'    => 'email',
                'identifier' => $this->user->email,
                'user_id'    => $this->user->id,
                'success'    => false,
            ]
        );

        $last = $this->user->lastLogin();

        $this->assertInstanceOf(Login::class, $last); // @phpstan-ignore-line
        $this->assertSame($login2->id, $last->id);
        $this->assertInstanceOf(Time::class, $last->date);
    }

    public function testPreviousLogin(): void
    {
        fake(
            UserIdentityModel::class,
            ['user_id' => $this->user->id, 'type' => Session::ID_TYPE_EMAIL_PASSWORD, 'secret' => 'foo@example.com']
        );

        // No logins found.
        $this->assertNull($this->user->previousLogin());

        $login1 = fake(
            LoginModel::class,
            ['id_type' => 'email', 'identifier' => $this->user->email, 'user_id' => $this->user->id]
        );

        // The very most login is skipped.
        $this->assertNull($this->user->previousLogin());

        $login2 = fake(
            LoginModel::class,
            ['id_type' => 'email', 'identifier' => $this->user->email, 'user_id' => $this->user->id]
        );
        fake(
            LoginModel::class,
            [
                'id_type'    => 'email',
                'identifier' => $this->user->email,
                'user_id'    => $this->user->id,
                'success'    => false,
            ]
        );

        $previous = $this->user->previousLogin();

        $this->assertInstanceOf(Login::class, $previous); // @phpstan-ignore-line
        $this->assertSame($login1->id, $previous->id);
        $this->assertInstanceOf(Time::class, $previous->date);
    }

    /**
     * @see https://github.com/codeigniter4/shield/issues/103
     */
    public function testUpdateEmail(): void
    {
        // Update user's email
        $this->user->email  = 'foo@bar.com';
        $this->user->active = 0;

        $users = model(UserModel::class);
        $users->save($this->user);

        $user = $users->find($this->user->id);

        $this->seeInDatabase('auth_identities', [
            'user_id' => $user->id,
            'secret'  => 'foo@bar.com',
        ]);
        $this->assertSame('foo@bar.com', $user->email);
    }

    /**
     * @see https://github.com/codeigniter4/shield/issues/103
     */
    public function testUpdatePassword(): void
    {
        // Update user's email
        $this->user->email    = 'foo@bar.com';
        $this->user->password = 'foobar';
        $this->user->active   = 0;

        $users = model(UserModel::class);
        $users->save($this->user);

        $user = $users->find($this->user->id);

        $this->assertTrue(service('passwords')->verify('foobar', $user->password_hash));
    }

    /**
     * @see https://github.com/codeigniter4/shield/issues/103
     */
    public function testUpdatePasswordHash(): void
    {
        // Update user's email
        $hash                      = service('passwords')->hash('foobar');
        $this->user->email         = 'foo@bar.com';
        $this->user->password_hash = $hash;
        $this->user->active        = 0;

        $users = model(UserModel::class);
        $users->save($this->user);

        $user = $users->find($this->user->id);

        $this->seeInDatabase('auth_identities', [
            'user_id' => $user->id,
            'secret'  => 'foo@bar.com',
            'secret2' => $hash,
        ]);
    }
}
