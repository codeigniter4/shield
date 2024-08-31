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
use CodeIgniter\Shield\Config\Auth;

class AddIndexToDeletedAtInUsers extends Migration
{
    /**
     * Auth Table names
     */
    private array $tables;

    public function __construct(?Forge $forge = null)
    {
        /** @var Auth $authConfig */
        $authConfig = config('Auth');

        if ($authConfig->DBGroup !== null) {
            $this->DBGroup = $authConfig->DBGroup;
        }

        parent::__construct($forge);

        $this->tables = $authConfig->tables;
    }

    /**
     * Apply the migration.
     *
     * This method adds an index to the `deleted_at` column of the `users` table.
     * It is called when running the `php spark migrate --all` command.
     */
    public function up(): void
    {
        $this->forge->addKey('deleted_at', false, false, 'deleted_at');
        $this->forge->processIndexes($this->tables['users']);
    }

    /**
     * Revert the migration.
     *
     * This method removes the index from the `deleted_at` column of the `users` table.
     * It is called when rolling back the migration using the `php spark migrate:rollback AddIndexToDeletedAtInUsers` command.
     */
    public function down(): void
    {
        // Drop index from the `deleted_at` field
        $this->forge->dropKey($this->tables['users'], 'deleted_at');
    }
}
