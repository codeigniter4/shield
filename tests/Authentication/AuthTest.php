<?php

namespace Tests\Authentication;

use CodeIgniter\Shield\Config\Services;
use Tests\Support\DatabaseTestCase;

/**
 * @internal
 */
final class AuthTest extends DatabaseTestCase
{
    public function testAuthenticateFail(): void
    {
        $auth = Services::auth();

        $credentials = ['email' => ''];
        $result      = $auth->authenticate($credentials);

        $this->assertFalse($result->isOK());
    }
}
