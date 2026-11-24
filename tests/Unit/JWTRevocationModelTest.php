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

namespace CodeIgniter\Shield\Tests\Unit;

use Closure;
use CodeIgniter\Database\Config as Database;
use CodeIgniter\Shield\Database\Migrations\CreateJWTRevocations;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException;
use CodeIgniter\Shield\Models\DatabaseException;
use CodeIgniter\Shield\Models\JWTRevocationModel;
use CodeIgniter\Shield\Tests\DatabaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;

/**
 * @internal
 */
final class JWTRevocationModelTest extends DatabaseTestCase
{
    protected $refresh = true;

    public function testRevocationIsVisibleToAnotherModel(): void
    {
        $writer     = new JWTRevocationModel();
        $identifier = hash('sha256', 'issuer and token identifier');

        $this->assertFalse($writer->isRevoked($identifier));

        $writer->revoke($identifier, 2_200_000_000);

        $this->assertTrue((new JWTRevocationModel())->isRevoked($identifier));
    }

    public function testDuplicateRevocationsKeepTheLaterExpiration(): void
    {
        $revocations = new JWTRevocationModel();
        $identifier  = hash('sha256', 'issuer and token identifier');

        $revocations->revoke($identifier, 1_900_000_000);
        $revocations->revoke($identifier, 2_200_000_000);
        $revocations->revoke($identifier, 2_200_000_000);
        $revocations->revoke($identifier, 1_900_000_000);

        $this->assertSame(0, $revocations->purgeExpired(1_900_000_000));
        $this->assertTrue($revocations->isRevoked($identifier));
        $this->assertSame(1, $revocations->purgeExpired(2_200_000_000));
        $this->assertFalse($revocations->isRevoked($identifier));
    }

    public function testMigrationAndModelUseConfiguredTableName(): void
    {
        $config                            = config('Auth');
        $table                             = 'custom_jwt_revocations';
        $config->tables['jwt_revocations'] = $table;
        $migration                         = new CreateJWTRevocations(Database::forge($this->db));
        $identifier                        = hash('sha256', 'issuer and token identifier');

        try {
            $migration->up();
            (new JWTRevocationModel())->revoke($identifier, 2_200_000_000);

            $this->seeInDatabase($table, ['identifier' => $identifier]);
            $this->dontSeeInDatabase($this->tables['jwt_revocations'], ['identifier' => $identifier]);
        } finally {
            $config->tables = $this->tables;
            $migration->down();
        }
    }

    public function testModelUsesDefaultTableWhenLegacyConfigOmitsTheKey(): void
    {
        $config     = config('Auth');
        $identifier = hash('sha256', 'issuer and token identifier');

        try {
            unset($config->tables['jwt_revocations']);

            (new JWTRevocationModel())->revoke($identifier, 2_200_000_000);

            $this->seeInDatabase($this->tables['jwt_revocations'], ['identifier' => $identifier]);
        } finally {
            $config->tables = $this->tables;
        }
    }

    public function testRawTokenIdentifiersAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new JWTRevocationModel())->revoke('raw token identifier', 2_200_000_000);
    }

    /**
     * @param Closure(JWTRevocationModel): void $query
     */
    #[DataProvider('provideDatabaseFailureThrowsWithDebugDisabled')]
    public function testDatabaseFailureThrowsWithDebugDisabled(Closure $query): void
    {
        $config    = config('Auth');
        $dbDebug   = new ReflectionProperty($this->db, 'DBDebug');
        $debugMode = $dbDebug->getValue($this->db);

        $this->expectException(DatabaseException::class);

        try {
            $config->tables['jwt_revocations'] = 'missing_jwt_revocations';
            $dbDebug->setValue($this->db, false);

            $query(new JWTRevocationModel());
        } finally {
            $config->tables = $this->tables;
            $dbDebug->setValue($this->db, $debugMode);
        }
    }

    /**
     * @return iterable<string, array{Closure(JWTRevocationModel): void}>
     */
    public static function provideDatabaseFailureThrowsWithDebugDisabled(): iterable
    {
        $identifier = hash('sha256', 'issuer and token identifier');

        yield 'read' => [static function (JWTRevocationModel $revocations) use ($identifier): void {
            $revocations->isRevoked($identifier);
        }];

        yield 'write' => [static function (JWTRevocationModel $revocations) use ($identifier): void {
            $revocations->revoke($identifier, 2_200_000_000);
        }];

        yield 'purge' => [static function (JWTRevocationModel $revocations): void {
            $revocations->purgeExpired(2_200_000_000);
        }];
    }
}
