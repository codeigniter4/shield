<?php

namespace Tests\Unit;

use CodeIgniter\Shield\Exceptions\RuntimeException;
use CodeIgniter\Shield\Models\LoginModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class LoginModelTest extends TestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    protected $refresh = true;

    private function createLoginModel(): LoginModel
    {
        return new LoginModel();
    }

    public function testRecordLoginAttemptThrowsException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Validation error: [ip_address] The ip_address field is required.'
            . ' [id_type] The id_type field is required.'
        );

        $model = $this->createLoginModel();

        $model->recordLoginAttempt(
            '',
            '',
            true
        );
    }
}
