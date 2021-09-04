<?php

use CodeIgniter\Test\CIUnitTestCase;
use Sparks\Shield\Entities\User;
use Sparks\Shield\Config\Auth;
use Sparks\Shield\Authentication\Passwords\NothingPersonalValidator;

class NothingPersonalValidatorTest extends CIUnitTestCase
{
	/**
	 * @var NothingPersonalValidator
	 */
	protected $validator;

	public function setUp(): void
	{
		parent::setUp();

		$config = new Auth();

		$this->validator = new NothingPersonalValidator();
		$this->validator->setConfig($config);
	}

	public function testFalseOnPasswordIsEmail()
	{
		$user = new User([
			'email'    => 'JoeSmith@example.com',
			'username' => 'Joe Smith',
		]);

		$password = 'joesmith@example.com';

		$result = $this->validator->check($password, $user);

		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.errorPasswordPersonal'), $result->reason());
		$this->assertEquals(lang('Auth.suggestPasswordPersonal'), $result->extraInfo());
	}

	public function testFalseOnPasswordIsUsernameBackwards()
	{
		$user = new \Sparks\Shield\Entities\User([
			'email'    => 'JoeSmith@example.com',
			'username' => 'Joe Smith',
		]);

		$password = 'Htims Eoj';

		$result = $this->validator->check($password, $user);

		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.errorPasswordPersonal'), $result->reason());
		$this->assertEquals(lang('Auth.suggestPasswordPersonal'), $result->extraInfo());
	}

	public function testFalseOnPasswordAndUsernameTheSame()
	{
		$user = new \Sparks\Shield\Entities\User([
			'email'    => 'vampire@example.com',
			'username' => 'Vlad the Impaler',
		]);

		$password = 'Vlad the Impaler';

		$result = $this->validator->check($password, $user);

		$this->assertFalse($result->isOK());
		$this->assertEquals(lang('Auth.errorPasswordPersonal'), $result->reason());
		$this->assertEquals(lang('Auth.suggestPasswordPersonal'), $result->extraInfo());
	}

	public function testTrueWhenPasswordHasNothingPersonal()
	{
		$config                 = new \Sparks\Shield\Config\Auth();
		$config->maxSimilarity  = 50;
		$config->personalFields = [
			'firstname',
			'lastname',
		];
		$this->validator->setConfig($config);

		$user = new \Sparks\Shield\Entities\User([
			'email'     => 'jsmith@example.com',
			'username'  => 'JoeS',
			'firstname' => 'Joseph',
			'lastname'  => 'Smith',
		]);

		$password = 'opensesame';

		$result = $this->validator->check($password, $user);

		$this->assertTrue($result->isOK());
	}

	/**
	 * The dataProvider is a list of passwords to be tested.
	 * Some of them clearly contain elements of the username.
	 * Others are scrambled usernames that may not clearly be troublesome,
	 * but arguably should considered troublesome.
	 *
	 * All the passwords are accepted by isNotPersonal() but are
	 * rejected by isNotSimilar().
	 *
	 *  $config->maxSimilarity = 50; is the highest setting where all tests pass.
	 *
	 * @dataProvider passwordProvider
	 */
	public function testIsNotPersonalFalsePositivesCaughtByIsNotSimilar($password)
	{
		$user = new \Sparks\Shield\Entities\User([
			'username' => 'CaptainJoe',
			'email'    => 'JosephSmith@example.com',
		]);

		$config                = new \Sparks\Shield\Config\Auth();
		$config->maxSimilarity = 50;
		$this->validator->setConfig($config);

		$isNotPersonal = $this->getPrivateMethodInvoker($this->validator, 'isNotPersonal', [$password, $user]); // @phpstan-ignore-line

		$isNotSimilar = $this->getPrivateMethodInvoker($this->validator, 'isNotSimilar', [$password, $user]); // @phpstan-ignore-line

		$this->assertNotSame($isNotPersonal, $isNotSimilar);
	}

	public function passwordProvider()
	{
		return [
			['JoeTheCaptain'],
			['JoeCaptain'],
			['CaptainJ'],
			['captainjoseph'],
			['captjoeain'],
			['tajipcanoe'],
			['jcaptoeain'],
			['jtaincapoe'],
		];
	}

	/**
	 * @dataProvider firstLastNameProvider
	 */
	public function testConfigPersonalFieldsValues($firstName, $lastName, $expected)
	{
		$config                 = new \Sparks\Shield\Config\Auth();
		$config->maxSimilarity  = 66;
		$config->personalFields = [
			'firstname',
			'lastname',
		];
		$this->validator->setConfig($config);

		$user = new \Sparks\Shield\Entities\User([
			'username'  => 'Vlad the Impaler',
			'email'     => 'vampire@example.com',
			'firstname' => $firstName,
			'lastname'  => $lastName,
		]);

		$password = 'Count Dracula';

		$result = $this->validator->check($password, $user);

		$this->assertSame($expected, $result->isOK());
	}

	public function firstLastNameProvider()
	{
		return [
			[
				'Count',
				'',
				false,
			],
			[
				'',
				'Dracula',
				false,
			],
			[
				'Vlad',
				'the Impaler',
				true,
			],
		];
	}

	/**
	 * @dataProvider maxSimilarityProvider
	 *
	 * The calculated similarity of 'captnjoe' and 'CaptainJoe' is 88.89.
	 * With $config->maxSimilarity = 66; the password should be rejected,
	 * but using $config->maxSimilarity = 0; will turn off the calculation
	 * and accept the password.
	 */
	public function testMaxSimilarityZeroTurnsOffSimilarityCalculation($maxSimilarity, $expected)
	{
		$config                = new \Sparks\Shield\Config\Auth();
		$config->maxSimilarity = $maxSimilarity;
		$this->validator->setConfig($config);

		$user = new \Sparks\Shield\Entities\User([
			'username' => 'CaptainJoe',
			'email'    => 'joseph@example.com',
		]);

		$password = 'captnjoe';

		$result = $this->validator->check($password, $user);

		$this->assertSame($expected, $result->isOK());
	}

	public function maxSimilarityProvider()
	{
		return[
			[
				66,
				false,
			],            [
				0,
				true,
			],
		];
	}

}
