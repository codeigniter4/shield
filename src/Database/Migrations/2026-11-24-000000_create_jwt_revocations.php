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

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;

class CreateJWTRevocations extends Migration
{
    private readonly string $table;

    public function __construct(?Forge $forge = null)
    {
        $this->DBGroup = config('Auth')->DBGroup;

        parent::__construct($forge);

        $this->table = config('AuthJWT')->revocationTable;
    }

    public function up(): void
    {
        $this->forge->addField([
            'identifier' => ['type' => 'varchar', 'constraint' => 64],
            'expires_at' => ['type' => 'bigint'],
        ]);
        $this->forge->addPrimaryKey('identifier');
        $this->forge->addKey('expires_at');

        $attributes = $this->db->getPlatform() === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : [];
        $this->forge->createTable($this->table, false, $attributes);
    }

    public function down(): void
    {
        $this->forge->dropTable($this->table, true);
    }
}
