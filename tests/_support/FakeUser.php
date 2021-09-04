<?php

namespace Tests\Support;

use Sparks\Shield\Entities\User;
use Sparks\Shield\Models\UserModel;

trait FakeUser
{
	/**
	 * @var User
	 */
	private $user;

	protected function setUpFakeUser()
	{
		$this->user = fake(UserModel::class);
	}
}
