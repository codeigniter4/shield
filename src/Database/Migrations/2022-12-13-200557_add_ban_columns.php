<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBanColumns extends Migration
{
    public function up(): void
    {
        // Users Table
        $fields = [
            'banned'      => ['type' => 'tinyint', 'after' => 'active', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'ban_message' => ['type' => 'varchar', 'after' => 'banned', 'constraint' => 255, 'null' => true],
        ];

        $this->forge->addColumn('users', $fields);
    }

    // --------------------------------------------------------------------

    public function down(): void
    {
        $this->forge->dropColumn('users', 'banned');
        $this->forge->dropColumn('users', 'ban_message');
    }
}
