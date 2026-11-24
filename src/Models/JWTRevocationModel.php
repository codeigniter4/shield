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

namespace CodeIgniter\Shield\Models;

use CodeIgniter\Database\RawSql;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException;

class JWTRevocationModel extends BaseModel
{
    protected $primaryKey       = 'identifier';
    protected $useAutoIncrement = false;
    protected $allowedFields    = ['identifier', 'expires_at'];

    protected function initialize(): void
    {
        parent::initialize();

        $this->table = $this->tables['jwt_revocations'] ?? 'auth_jwt_revocations';
    }

    public function revoke(string $identifier, int $expiresAt): void
    {
        if (preg_match('/\A[0-9a-f]{64}\z/', $identifier) !== 1) {
            throw new InvalidArgumentException('Expected a SHA-256 digest for the JWT revocation identifier.');
        }

        $expiration = $this->db->protectIdentifiers($this->table . '.expires_at');
        $later      = new RawSql("CASE WHEN {$expiration} < {$expiresAt} THEN {$expiresAt} ELSE {$expiration} END");

        // Execute the compiled upsert directly to observe write failures even when DBDebug is false.
        $sql = $this->db->table($this->table)
            ->onConstraint('identifier')
            ->set(['identifier' => $identifier, 'expires_at' => $expiresAt])
            ->updateFields(['expires_at' => $later])
            ->getCompiledUpsert();

        $this->checkQueryReturn($this->db->query($sql, null, false) !== false);
    }

    public function isRevoked(string $identifier): bool
    {
        $result = $this->builder()
            ->select('identifier')
            ->where('identifier', $identifier)
            ->get();

        if ($result === false) {
            throw new DatabaseException('Could not read the JWT revocation table.');
        }

        return $result->getRowArray() !== null;
    }

    public function purgeExpired(int $now): int
    {
        $deleted = $this->builder()
            ->where('expires_at <=', $now)
            ->delete();
        $this->checkQueryReturn($deleted);

        return $this->db->affectedRows();
    }
}
