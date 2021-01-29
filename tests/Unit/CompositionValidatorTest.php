<?php

use Tests\Support\TestCase;
use Sparks\Shield\Authentication\Passwords\CompositionValidator;
use Sparks\Shield\Config\Auth;
use Sparks\Shield\Authentication\AuthenticationException;

class CompositionValidatorTest extends TestCase
{
	/**
	 * @var CompositionValidator
	 */
	protected $validator;

	/**
	 * @var \Sparks\Shield\Config\Auth
	 */
	protected $config;

	public function setUp(): void
	{
		parent::setUp();

		$this->config                        = new Auth();
		$this->config->minimumPasswordLength = 8;

		$this->validator = new CompositionValidator();
		$this->validator->setConfig($this->config);
	}

	public function testCheckThrowsException()
	{
		$this->expectException(AuthenticationException::class);
		$this->expectExceptionMessage(lang('Auth.unsetPasswordLength'));

		$password                            = '1234';
		$this->config->minimumPasswordLength = 0;
		$this->validator->setConfig($this->config);

		$result = $this->validator->check($password);
	}

	public function testCheckFalse()
	{
		$password = '1234';

		$result = $this->validator->check($password);

		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.errorPasswordLength', [$this->config->minimumPasswordLength]), $result->reason());
	}

	public function testCheckTrue()
	{
		$password = '1234567890';

		$result = $this->validator->check($password);

		$this->assertTrue($result->isOK());
	}
}
