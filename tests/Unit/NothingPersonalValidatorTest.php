<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\Passwords\NothingPersonalValidator;
use CodeIgniter\Shield\Config\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
final class NothingPersonalValidatorTest extends CIUnitTestCase
{
    private NothingPersonalValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new NothingPersonalValidator(new Auth());
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
        $config->personalFields = ['firstname', 'lastname'];
        $this->validator        = new NothingPersonalValidator($config);

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
        $config->personalFields = ['firstname', 'lastname'];
        $this->validator        = new NothingPersonalValidator($config);

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
     */
    #[DataProvider('provideIsNotPersonalFalsePositivesCaughtByIsNotSimilar')]
    public function testIsNotPersonalFalsePositivesCaughtByIsNotSimilar(string $password): void
    {
        $user = new User([
            'username' => 'CaptainJoe',
            'email'    => 'JosephSmith@example.com',
        ]);

        $config                = new Auth();
        $config->maxSimilarity = 50;
        $this->validator       = new NothingPersonalValidator($config);

        $isNotPersonal = self::getPrivateMethodInvoker($this->validator, 'isNotPersonal');

        $isNotSimilar = self::getPrivateMethodInvoker($this->validator, 'isNotSimilar');

        $this->assertNotSame($isNotPersonal($password, $user), $isNotSimilar($password, $user));
    }

    /**
     * @return iterable<int, array{0: string}>
     */
    public static function provideIsNotPersonalFalsePositivesCaughtByIsNotSimilar(): iterable
    {
        yield from [
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

    #[DataProvider('provideConfigPersonalFieldsValues')]
    public function testConfigPersonalFieldsValues(string $firstName, string $lastName, bool $expected): void
    {
        $config                 = new Auth();
        $config->maxSimilarity  = 66;
        $config->personalFields = ['firstname', 'lastname'];
        $this->validator        = new NothingPersonalValidator($config);

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

    /**
     * @return iterable<int, array{0: string, 1: string, 2: bool}>
     */
    public static function provideConfigPersonalFieldsValues(): iterable
    {
        yield from [
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

    #[DataProvider('provideMaxSimilarityZeroTurnsOffSimilarityCalculation')]
    public function testMaxSimilarityZeroTurnsOffSimilarityCalculation(int $maxSimilarity, bool $expected): void
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

    /**
     * @return iterable<int, array{0: int, 1: bool}>
     */
    public static function provideMaxSimilarityZeroTurnsOffSimilarityCalculation(): iterable
    {
        return [
            [
                66,
                false,
            ],
            [
                0,
                true,
            ],
        ];
    }

    #[DataProvider('provideCheckPasswordWithBadEmail')]
    public function testCheckPasswordWithBadEmail(string $email, bool $expected): void
    {
        $config          = new Auth();
        $this->validator = new NothingPersonalValidator($config);

        $user = new User([
            'username' => 'CaptainJoe',
            'email'    => $email,
        ]);

        $password = '123456789a';

        $result = $this->validator->check($password, $user);

        $this->assertSame($expected, $result->isOK());
    }

    /**
     * @return iterable<int, array{0: string, 1: bool}>
     */
    public static function provideCheckPasswordWithBadEmail(): iterable
    {
        return [
            [
                'test',
                true,
            ],
            [
                'test@example',
                true,
            ],
            [
                'test@example.com',
                true,
            ],
        ];
    }
}
