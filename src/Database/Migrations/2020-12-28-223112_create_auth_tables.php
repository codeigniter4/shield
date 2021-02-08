<?php

namespace Sparks\Shield\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTables extends Migration
{
	public function up()
	{
		/*
		 * Users
		 */
		$this->forge->addField([
			'id'             => [
				'type'           => 'int',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'username'       => [
				'type'       => 'varchar',
				'constraint' => 30,
				'null'       => true,
			],
			'status'         => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'status_message' => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'active'         => [
				'type'       => 'tinyint',
				'constraint' => 1,
				'null'       => 0,
				'default'    => 0,
			],
			'created_at'     => [
				'type' => 'datetime',
				'null' => true,
			],
			'updated_at'     => [
				'type' => 'datetime',
				'null' => true,
			],
			'deleted_at'     => [
				'type' => 'datetime',
				'null' => true,
			],
		]);

		$this->forge->addPrimaryKey('id');
		$this->forge->addUniqueKey('email');
		$this->forge->addUniqueKey('username');
		$this->forge->createTable('users', true);

		/*
		 * Auth Identities
		 * Used for storage of passwords, reset hashes
		 * social login identities, etc.
		 */
		$this->forge->addField([
			'id'           => [
				'type'           => 'int',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'user_id'      => [
				'type'       => 'int',
				'constraint' => 11,
				'unsigned'   => true,
				'null'       => true,
			],
			'type'         => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'name'         => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'secret'       => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'secret2'      => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'expires'      => [
				'type' => 'datetime',
				'null' => true,
			],
			'extra'        => [
				'type' => 'text',
				'null' => true,
			],
			'force_reset'  => [
				'type'       => 'tinyint',
				'constraint' => 1,
				'default'    => 0,
			],
			'last_used_at' => [
				'type' => 'datetime',
				'null' => true,
			],
			'created_at'   => [
				'type' => 'datetime',
				'null' => true,
			],
			'updated_at'   => [
				'type' => 'datetime',
				'null' => true,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->addKey('user_id');
		$this->forge->addForeignKey('user_id', 'users', 'id', false, 'CASCADE');
		$this->forge->createTable('auth_identities', true);

		/*
		 * Auth Login Attempts
		 */
		$this->forge->addField([
			'id'         => [
				'type'           => 'int',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'ip_address' => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'email'      => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'user_id'    => [
				'type'       => 'int',
				'constraint' => 11,
				'unsigned'   => true,
				'null'       => true,
			], // Only for successful logins
			'date'       => ['type' => 'datetime'],
			'success'    => [
				'type'       => 'tinyint',
				'constraint' => 1,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->addKey('email');
		$this->forge->addKey('user_id');
		// NOTE: Do NOT delete the user_id or email when the user is deleted for security audits
		$this->forge->createTable('auth_logins', true);

		/*
		 * Auth Tokens  (Remember Me)
		 * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
		 */
		$this->forge->addField([
			'id'              => [
				'type'           => 'int',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'selector'        => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'hashedValidator' => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'user_id'         => [
				'type'       => 'int',
				'constraint' => 11,
				'unsigned'   => true,
			],
			'expires'         => ['type' => 'datetime'],
			'created_at'      => [
				'type' => 'datetime',
				'null' => false,
			],
			'updated_at'      => [
				'type' => 'datetime',
				'null' => false,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->addUniqueKey('selector');
		$this->forge->addForeignKey('user_id', 'users', 'id', false, 'CASCADE');
		$this->forge->createTable('auth_remember_tokens', true);

		/*
		 * Password Reset Table
		 */
		$this->forge->addField([
			'id'         => [
				'type'           => 'int',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'email'      => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'ip_address' => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'user_agent' => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'token'      => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'created_at' => [
				'type' => 'datetime',
				'null' => false,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->createTable('auth_reset_attempts', true);

		/*
		 * Activation Attempts Table
		 */
		$this->forge->addField([
			'id'         => [
				'type'           => 'int',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'ip_address' => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'user_agent' => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'token'      => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'created_at' => [
				'type' => 'datetime',
				'null' => false,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->createTable('auth_activation_attempts', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$prefix = $this->db->getPrefix();
		$prefix = empty($prefix) ? $prefix : $prefix . '_';

		// drop constraints first to prevent errors
		if ($this->db->tableExists('auth_remember_tokens'))
		{
			$this->forge->dropForeignKey('auth_remember_tokens', 'auth_remember_tokens_user_id_foreign');
		}
		if ($this->db->tableExists('auth_identities'))
		{
			$this->forge->dropForeignKey('auth_identities', 'auth_identities_user_id_foreign');
			$this->db->query('DROP INDEX IF EXISTS ?', [$prefix . 'auth_identities_user_id']);
		}
		if ($this->db->tableExists('users'))
		{
			$this->db->query('DROP INDEX IF EXISTS ?', [$prefix . 'users_username']);
		}

		$this->forge->dropTable('users', true);
		$this->forge->dropTable('auth_logins', true);
		$this->forge->dropTable('auth_remember_tokens', true);
		$this->forge->dropTable('auth_reset_attempts', true);
		$this->forge->dropTable('auth_activation_attempts', true);
		$this->forge->dropTable('auth_access_tokens', true);
		$this->forge->dropTable('auth_identities', true);
	}
}
