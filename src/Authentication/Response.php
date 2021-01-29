<?php

namespace Sparks\Shield\Authentication;

class Response
{
	protected $error;

	protected $extraInfo;

	protected $user;

	/**
	 * Was the user successfully authenticated?
	 *
	 * @return boolean
	 */
	public function isOk()
	{
		return $this->user !== null;
	}

	public function __set(string $key, $value = null)
	{
		if (property_exists($this, $key))
		{
			$this->$key = $value;
		}
	}

	public function __get(string $key)
	{
		if (property_exists($this, $key))
		{
			return $this->$key;
		}
	}
}
