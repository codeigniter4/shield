<?php

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTables extends Migration
{
    public function up(): void
    {
        // Users Table
        $this->forge->addField([
            'id'             => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'username'       => ['type' => 'varchar', 'constraint' => 30, 'null' => true],
            'status'         => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'status_message' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'active'         => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 0],
            'last_active'    => ['type' => 'datetime', 'null' => true],
            'created_at'     => ['type' => 'datetime', 'null' => true],
            'updated_at'     => ['type' => 'datetime', 'null' => true],
            'deleted_at'     => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users', true);

        /*
         * Auth Identities Table
         * Used for storage of passwords, access tokens, social login identities, etc.
         */
        $this->forge->addField([
            'id'           => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'type'         => ['type' => 'varchar', 'constraint' => 255],
            'name'         => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'secret'       => ['type' => 'varchar', 'constraint' => 255],
            'secret2'      => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'expires'      => ['type' => 'datetime', 'null' => true],
            'extra'        => ['type' => 'text', 'null' => true],
            'force_reset'  => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'last_used_at' => ['type' => 'datetime', 'null' => true],
            'created_at'   => ['type' => 'datetime', 'null' => true],
            'updated_at'   => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['type', 'secret']);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('auth_identities', true);

        /**
         * Auth Login Attempts Table
         * Records login attempts. A login means users think it is a login.
         * To login, users do action(s) like posting a form.
         */
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address' => ['type' => 'varchar', 'constraint' => 255],
            'user_agent' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'id_type'    => ['type' => 'varchar', 'constraint' => 255],
            'identifier' => ['type' => 'varchar', 'constraint' => 255],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true], // Only for successful logins
            'date'       => ['type' => 'datetime'],
            'success'    => ['type' => 'tinyint', 'constraint' => 1],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['id_type', 'identifier']);
        $this->forge->addKey('user_id');
        // NOTE: Do NOT delete the user_id or identifier when the user is deleted for security audits
        $this->forge->createTable('auth_logins', true);

        /*
         * Auth Token Login Attempts Table
         * Records Bearer Token type login attempts.
         */
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ip_address' => ['type' => 'varchar', 'constraint' => 255],
            'user_agent' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'id_type'    => ['type' => 'varchar', 'constraint' => 255],
            'identifier' => ['type' => 'varchar', 'constraint' => 255],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true], // Only for successful logins
            'date'       => ['type' => 'datetime'],
            'success'    => ['type' => 'tinyint', 'constraint' => 1],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['id_type', 'identifier']);
        $this->forge->addKey('user_id');
        // NOTE: Do NOT delete the user_id or identifier when the user is deleted for security audits
        $this->forge->createTable('auth_token_logins', true);

        /*
         * Auth Remember Tokens (remember-me) Table
         * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
         */
        $this->forge->addField([
            'id'              => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'selector'        => ['type' => 'varchar', 'constraint' => 255],
            'hashedValidator' => ['type' => 'varchar', 'constraint' => 255],
            'user_id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'expires'         => ['type' => 'datetime'],
            'created_at'      => ['type' => 'datetime', 'null' => false],
            'updated_at'      => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('selector');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('auth_remember_tokens', true);

        // Groups Users Table
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'group'      => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'created_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('auth_groups_users', true);

        // Users Permissions Table
        $this->forge->addField([
            'id'         => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'permission' => ['type' => 'varchar', 'constraint' => 255, 'null' => false],
            'created_at' => ['type' => 'datetime', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('auth_permissions_users', true);
    }

    //--------------------------------------------------------------------

    public function down(): void
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('auth_logins', true);
        $this->forge->dropTable('auth_token_logins', true);
        $this->forge->dropTable('auth_remember_tokens', true);
        $this->forge->dropTable('auth_identities', true);
        $this->forge->dropTable('auth_groups_users', true);
        $this->forge->dropTable('auth_permissions_users', true);
        $this->forge->dropTable('users', true);

        $this->db->enableForeignKeyChecks();
    }
}
