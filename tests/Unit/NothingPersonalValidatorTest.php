<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\Passwords\NothingPersonalValidator;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class NothingPersonalValidatorTest extends CIUnitTestCase
{
    private NothingPersonalValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $config          = new Auth();
        $this->validator = new NothingPersonalValidator($config);
    }

    public function testFalseOnPasswordIsEmail(): void
    {
        $user = new User([
            'email'    => 'JoeSmith@example.com',
            'username' => 'Joe Smith',
        ]);

        $password = 'joesmith@example.com';

        $result = $this->validator->check($password, $user);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.errorPasswordPersonal'), $result->reason());
        $this->assertSame(lang('Auth.suggestPasswordPersonal'), $result->extraInfo());
    }

    public function testFalseOnPasswordIsUsernameBackwards(): void
    {
        $user = new User([
            'email'    => 'JoeSmith@example.com',
            'username' => 'Joe Smith',
        ]);

        $password = 'Htims Eoj';

        $result = $this->validator->check($password, $user);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.errorPasswordPersonal'), $result->reason());
        $this->assertSame(lang('Auth.suggestPasswordPersonal'), $result->extraInfo());
    }

    public function testFalseOnPasswordAndUsernameTheSame(): void
    {
        $user = new User([
            'email'    => 'vampire@example.com',
            'username' => 'Vlad the Impaler',
        ]);

        $password = 'Vlad the Impaler';

        $result = $this->validator->check($password, $user);

        $this->assertFalse($result->isOK());
        $this->assertSame(lang('Auth.errorPasswordPersonal'), $result->reason());
        $this->assertSame(lang('Auth.suggestPasswordPersonal'), $result->extraInfo());
    }

    public function testTrueWhenPasswordHasNothingPersonal(): void
    {
        $config                 = new Auth();
        $config->maxSimilarity  = 50;
        $config->personalFields = [
            'firstname',
            'lastname',
        ];
        $this->validator = new NothingPersonalValidator($config);

        $user = new User([
            'email'     => 'jsmith@example.com',
            'username'  => 'JoeS',
            'firstname' => 'Joseph',
            'lastname'  => 'Smith',
        ]);

        $password = 'opensesame';

        $result = $this->validator->check($password, $user);

        $this->assertTrue($result->isOK());
    }

    public function testTrueWhenNoUsername(): void
    {
        $config                 = new Auth();
        $config->maxSimilarity  = 50;
        $config->personalFields = [
            'firstname',
            'lastname',
        ];
        $this->validator = new NothingPersonalValidator($config);

        $user = new User([
            'email'     => 'jsmith@example.com',
            'firstname' => 'Joseph',
            'lastname'  => 'Smith',
        ]);

        $password = 'opensesame';

        $result = $this->validator->check($password, $user);

        $this->assertTrue($result->isOK());
    }

    public function testTrueForAllowedTooSmallMatch(): void
    {
        $user = new User([
            'email'    => 'xxx@example.com',
            'username' => 'john doe',
        ]);

        $password = 'xx-test@123';

        $result = $this->validator->check($password, $user);

        $this->assertTrue($result->isOK());
    }

    public function testFalseForSensibleMatch(): void
    {
        $user = new User([
            'email'    => 'xxx@example.com',
            'username' => 'john doe',
        ]);

        $password = 'xxx-test@123';

        $result = $this->validator->check($password, $user);

        $this->assertFalse($result->isOK());
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
     *
     * @param mixed $password
     */
    public function testIsNotPersonalFalsePositivesCaughtByIsNotSimilar($password): void
    {
        new User([
            'username' => 'CaptainJoe',
            'email'    => 'JosephSmith@example.com',
        ]);

        $config                = new Auth();
        $config->maxSimilarity = 50;
        $this->validator       = new NothingPersonalValidator($config);

        $isNotPersonal = $this->getPrivateMethodInvoker($this->validator, 'isNotPersonal');

        $isNotSimilar = $this->getPrivateMethodInvoker($this->validator, 'isNotSimilar');

        $this->assertNotSame($isNotPersonal, $isNotSimilar);
    }

    public static function passwordProvider(): array
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
     *
     * @param mixed $firstName
     * @param mixed $lastName
     * @param mixed $expected
     */
    public function testConfigPersonalFieldsValues($firstName, $lastName, $expected): void
    {
        $config                 = new Auth();
        $config->maxSimilarity  = 66;
        $config->personalFields = [
            'firstname',
            'lastname',
        ];
        $this->validator = new NothingPersonalValidator($config);

        $user = new User([
            'username'  => 'Vlad the Impaler',
            'email'     => 'vampire@example.com',
            'firstname' => $firstName,
            'lastname'  => $lastName,
        ]);

        $password = 'Count Dracula';

        $result = $this->validator->check($password, $user);

        $this->assertSame($expected, $result->isOK());
    }

    public static function firstLastNameProvider()
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
     *
     * @param mixed $maxSimilarity
     * @param mixed $expected
     */
    public function testMaxSimilarityZeroTurnsOffSimilarityCalculation($maxSimilarity, $expected): void
    {
        $config                = new Auth();
        $config->maxSimilarity = $maxSimilarity;
        $this->validator       = new NothingPersonalValidator($config);

        $user = new User([
            'username' => 'CaptainJoe',
            'email'    => 'joseph@example.com',
        ]);

        $password = 'captnjoe';

        $result = $this->validator->check($password, $user);

        $this->assertSame($expected, $result->isOK());
    }

    public static function maxSimilarityProvider()
    {
        return [
            [
                66,
                false,
            ], [
                0,
                true,
            ],
        ];
    }
}
