<?php

namespace Sparks\Shield\Test;

use Sparks\Shield\Entities\User;

/**
 * Trait AuthenticationTesting
 *
 * Helper methods for testing using Shield.
 *
 * @package Sparks\Shield\Test
 */
trait AuthenticationTesting
{
	/**
	 * Logs the user for testing purposes.
	 *
	 * @param \Sparks\Shield\Entities\User $user
	 *
	 * @return $this
	 */
	public function actingAs(User $user)
	{
		auth('session')->login($user);

		return $this;
	}
}
