<?php

use CodeIgniter\Test\CIUnitTestCase;
use Sparks\Shield\Authentication\Passwords\DictionaryValidator;
use Sparks\Shield\Config\Auth as AuthConfig;

class DictionaryValidatorTest extends CIUnitTestCase
{
	/**
	 * @var CompositionValidator
	 */
	protected $validator;

	public function setUp(): void
	{
		parent::setUp();

		$config = new AuthConfig();

		$this->validator = new DictionaryValidator();
		$this->validator->setConfig($config);
	}

	public function testCheckFalseOnFoundPassword()
	{
		$password = '!!!gerard!!!';

		$result = $this->validator->check($password);

		$this->assertFalse($result->isOK());
	}

	public function testCheckTrueOnNotFound()
	{
		$password = '!!!gerard!!!abootylicious';

		$result = $this->validator->check($password);

		$this->assertTrue($result->isOK());
	}

}
