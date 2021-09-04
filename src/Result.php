<?php

namespace Sparks\Shield;

class Result
{
	/**
	 * @var boolean
	 */
	protected $success = false;

	/**
	 * Provides a simple explanation of
	 * the error that happened.
	 * Typically a single sentence.
	 *
	 * @var string
	 */
	protected $reason;

	/**
	 * Extra information available to describe
	 * the error. No particular format is
	 * specified.
	 *
	 * @var mixed
	 */
	protected $extraInfo;

	public function __construct(array $details)
	{
		foreach ($details as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
	}

	/**
	 * Was the result a success?
	 *
	 * @return boolean
	 */
	public function isOK()
	{
		return $this->success;
	}

	/**
	 * @return string
	 */
	public function reason()
	{
		return $this->reason;
	}

	/**
	 * @return mixed
	 */
	public function extraInfo()
	{
		return $this->extraInfo;
	}
}
