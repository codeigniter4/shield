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

use CodeIgniter\Shield\Authentication\Passwords\DictionaryValidator;
use CodeIgniter\Shield\Config\Auth as AuthConfig;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DictionaryValidatorTest extends CIUnitTestCase
{
    private DictionaryValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new AuthConfig();

        $this->validator = new DictionaryValidator($config);
    }

    public function testCheckFalseOnFoundPassword(): void
    {
        $password = '!!!gerard!!!';

        $result = $this->validator->check($password);

        $this->assertFalse($result->isOK());
    }

    public function testCheckTrueOnNotFound(): void
    {
        $password = '!!!gerard!!!abootylicious';

        $result = $this->validator->check($password);

        $this->assertTrue($result->isOK());
    }
}
