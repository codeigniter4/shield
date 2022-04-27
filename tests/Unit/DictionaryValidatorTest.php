<?php

namespace Tests\Unit;

use CodeIgniter\Shield\Authentication\Passwords\DictionaryValidator;
use CodeIgniter\Shield\Config\Auth as AuthConfig;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DictionaryValidatorTest extends CIUnitTestCase
{
    protected DictionaryValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $config = new AuthConfig();

        $this->validator = new DictionaryValidator($config);
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
