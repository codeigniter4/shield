<?php

use CodeIgniter\Test\CIDatabaseTestCase;
use Sparks\Shield\Models\UserModel;
use Sparks\Shield\Entities\UserIdentity;
use Sparks\Shield\Models\UserIdentityModel;

class UserTest extends CIDatabaseTestCase
{
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
}
