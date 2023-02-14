<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Forge;
use CodeIgniter\Database\Migration;
use CodeIgniter\Shield\Config\Auth;

class AddBanColumns extends Migration
{
    /**
     * Auth Table names
     */
    private array $tables;

    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);

        /** @var Auth $authConfig */
        $authConfig   = config('Auth');
        $this->tables = $authConfig->tables;
    }

    public function up(): void
    {
        // Users Table
        $fields = [
            'banned'      => ['type' => 'tinyint', 'after' => 'active', 'constraint' => 1, 'null' => false, 'default' => 0],
            'ban_message' => ['type' => 'varchar', 'after' => 'banned', 'constraint' => 255, 'null' => true],
        ];

        $this->forge->addColumn($this->tables['users'], $fields);
    }

    // --------------------------------------------------------------------

    public function down(): void
    {
        $this->forge->dropColumn($this->tables['users'], 'banned');
        $this->forge->dropColumn($this->tables['users'], 'ban_message');
    }
}
