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

    public function testFalseWhenPasswordPartIsExactlyThreeCharactersLong(): void
    {
        $user = new User([
            'email'    => 'zzzz@example.com',
            'username' => 'bobby',
        ]);

        $result = $this->validator->check('bob!qzxwv7', $user);

        $this->assertFalse($result->isOK());
    }

    public function testTrueWhenPasswordIsTwoMultibyteCharacters(): void
    {
        $user = new User([
            'email'    => 'éé1@example.com',
            'username' => null,
        ]);

        $result = $this->validator->check('éé', $user);

        $this->assertTrue($result->isOK());
    }

    public function testTrueWhenPersonalNeedleIsTwoMultibyteCharacters(): void
    {
        $user = new User([
            'email'    => 'éé@example.com',
            'username' => null,
        ]);

        $result = $this->validator->check('éé1', $user);

        $this->assertTrue($result->isOK());
    }

    public function testChecksAreOverridableByExtendingClasses(): void
    {
        $validator = new class (new Auth()) extends NothingPersonalValidator {
            public function personal(string $password, ?User $user): bool
            {
                return $this->isNotPersonal($password, $user);
            }

            public function similar(string $password, ?User $user): bool
            {
                return $this->isNotSimilar($password, $user);
            }
        };

        $user = new User([
            'email'    => 'zzzz@example.com',
            'username' => 'bobby',
        ]);

        $this->assertTrue($validator->personal('opensesame', $user));
        $this->assertTrue($validator->similar('opensesame', $user));
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

    #[DataProvider('provideIsolatedMethodIsNotPersonal')]
    public function testIsolatedMethodIsNotPersonal(mixed ...$input): void
    {
        $user   = null;
        $fields = [];
        // Password removes spaces inside `check()`, so we’ll duplicate it here
        $password = mb_strtolower($input[0]);

        if (is_array($input[1])) {
            $user   = new User($input[1]);
            $fields = array_keys($input[1]);
        }

        $config                 = new Auth();
        $config->personalFields = array_filter($fields, static fn ($val): bool => $val !== 'email' && $val !== 'username');
        $validator              = new NothingPersonalValidator($config);

        $isNotPersonal = self::getPrivateMethodInvoker($validator, 'isNotPersonal');

        /** @var bool */
        $result = $isNotPersonal($password, $user);

        $this->assertSame($input[2], $result);
    }

    public static function provideIsolatedMethodIsNotPersonal(): iterable
    {
        // @todo: strrev() doesn’t work with Unicode
        // yield 'Password is equal to reversed username' => [
        //     'ПеТьКа',
        //     [
        //         'email' => 'аДмИн@СаЙт.Ру',
        //         'username' => 'аКьТеП'
        //     ],
        //     false
        // ];

        yield 'Empty password and username' => [
            '',
            [
                'email' => 'админ@сайт.ру',
            ],
            false,
        ];

        yield 'Password is equal to username' => [
            'ПаРоЛь',
            [
                'email'    => 'админ@сайт.ру',
                'username' => 'пАрОлЬ',
            ],
            false,
        ];

        yield 'Password is equal to email' => [
            'аДмИн@СаЙт.Ру',
            [
                'email'    => 'АдМиН@сАйТ.рУ',
                'username' => 'Петя',
            ],
            false,
        ];

        yield 'Password contains the username' => [
            'ПеТя-9000',
            [
                'email'    => 'админ@сайт.ру',
                'username' => 'пЕтЯ',
            ],
            false,
        ];

        yield 'Password contains the email' => [
            'ПеТя-9000',
            [
                'email'    => 'админ@петя.ру',
                'username' => 'Пётр',
            ],
            false,
        ];

        yield 'Password contains the personal data' => [
            '_ПеТр_9000',
            [
                'email'    => 'админ@caйт.ру',
                'username' => 'Пётр',
                'lastname' => 'Петров',
            ],
            false,
        ];

        yield 'Empty password' => [
            '',
            [
                'email'    => 'админ@сайт.ру',
                'username' => 'Петя',
            ],
            true,
        ];

        yield 'Only spaces in password' => [
            '          ',
            [
                'email'    => 'админ@сайт.ру',
                'username' => 'Петя',
            ],
            true,
        ];

        yield 'Password without user' => [
            '_ПеТр_9000',
            null,
            true,
        ];
    }

    #[DataProvider('provideIsolatedMethodIsNotSimilar')]
    public function testIsolatedMethodIsNotSimilar(mixed ...$input): void
    {
        $user = null;
        // Password removes spaces inside `check()`, so we’ll duplicate it here
        $password = mb_strtolower($input[0]);

        if (is_array($input[1])) {
            $user = new User($input[1]);
        }

        $config                = new Auth();
        $config->maxSimilarity = $input[2];
        $validator             = new NothingPersonalValidator($config);

        $isNotSimilar = self::getPrivateMethodInvoker($validator, 'isNotSimilar');

        /** @var bool */
        $result = $isNotSimilar($password, $user);

        $this->assertSame($input[3], $result);
    }

    public static function provideIsolatedMethodIsNotSimilar(): iterable
    {
        yield 'Same password and username (similar > 1%)' => [
            'ПёТр',
            [
                'username' => 'ПёТр',
            ],
            1,
            false,
        ];

        yield 'Same password and username (similar > 100%)' => [
            'ПёТр',
            [
                'username' => 'ПёТр',
            ],
            150,
            false,
        ];

        yield 'Empty password and username (similar < 1%)' => [
            '',
            [
                'username' => '',
            ],
            0,
            true,
        ];

        yield 'Empty password and username (similar > 1%)' => [
            '',
            [
                'username' => '',
            ],
            1,
            true,
        ];

        yield 'Empty password and specified username (similar > 1%)' => [
            '',
            [
                'username' => 'ПёТр',
            ],
            1,
            true,
        ];

        yield 'Empty username and specified password (similar > 1%)' => [
            'ПаРоЛь',
            [
                'username' => '',
            ],
            1,
            true,
        ];

        yield 'Empty User and specified password (similar > 1%)' => [
            'ПаРоЛь',
            null,
            1,
            true,
        ];
    }
}
