<?php

use Tests\Support\TestCase;
use Sparks\Shield\Models\UserModel;
use Sparks\Shield\Entities\UserIdentity;
use Sparks\Shield\Models\UserIdentityModel;
use Sparks\Shield\Models\LoginModel;
use CodeIgniter\Test\DatabaseTestTrait;

class UserTest extends TestCase
{
	use DatabaseTestTrait;

	protected $namespace = '\Sparks\Shield';

	protected $refresh = true;

	public function setUp(): void
	{
		parent::setUp();

		db_connect()->table('auth_identities')->truncate();
		db_connect()->table('users')->truncate();
	}

	public function testGetIdentitiesNone()
	{
		$user = fake(UserModel::class);

		// when none, returns empty array
		$this->assertEmpty($user->identities);
	}

	public function testGetIdentitiesSome()
	{
		$user = fake(UserModel::class);

		$passIdentity  = fake(UserIdentityModel::class, ['user_id' => $user->id, 'type' => 'password']);
		$tokenIdentity = fake(UserIdentityModel::class, ['user_id' => $user->id, 'type' => 'access_token']);

		$identities = $user->identities;
		$this->assertCount(2, $identities);
	}

	public function testGetIdentitiesByType()
	{
		$user = fake(UserModel::class);

		$passIdentity  = fake(UserIdentityModel::class, ['user_id' => $user->id, 'type' => 'password']);
		$tokenIdentity = fake(UserIdentityModel::class, ['user_id' => $user->id, 'type' => 'access_token']);

		$identities = $user->identitiesOfType('access_token');
		$this->assertCount(1, $identities);
		$this->assertInstanceOf(UserIdentity::class, $identities[0]);
		$this->assertEquals('access_token', $identities[0]->type);

		$this->assertEmpty($user->identitiesOfType('foo'));
	}

	public function testModelWithIdentities()
	{
		$user  = fake(UserModel::class);
		$user2 = fake(UserModel::class);

		$passIdentity  = fake(UserIdentityModel::class, ['user_id' => $user->id, 'type' => 'password']);
		$tokenIdentity = fake(UserIdentityModel::class, ['user_id' => $user->id, 'type' => 'access_token']);

		// Grab the user again, using the model's identity helper
		$users = model(UserModel::class)->withIdentities()->findAll();

		$identities = $user->identities;
		$this->assertCount(2, $identities);
	}

	public function testLastLogin()
	{
		$user          = fake(UserModel::class);
		$emailIdentity = fake(UserIdentityModel::class, ['user_id' => $user->id, 'type' => 'email_password', 'secret' => 'foo@example.com']);

		// No logins found.
		$this->assertNull($user->lastLogin());

		$login    = fake(LoginModel::class, ['email' => $user->email, 'user_id' => $user->id]);
		$badLogin = fake(LoginModel::class, ['email' => $user->email, 'user_id' => $user->id, 'success' => false]);

		$last = $user->lastLogin();
		$this->assertInstanceOf(\Sparks\Shield\Entities\Login::class, $last);
		$this->assertEquals($login->id, $last->id);
		$this->assertInstanceOf(\CodeIgniter\I18n\Time::class, $last->date);
	}
}
