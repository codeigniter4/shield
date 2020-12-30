<?php

namespace CodeIgniter\Shield\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTables extends Migration
{
	public function up()
	{
		/*
		 * Users
		 */
		$this->forge->addField([
			'id'               => [
				'type'           => 'int',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'email'            => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'username'         => [
				'type'       => 'varchar',
				'constraint' => 30,
				'null'       => true,
			],
			'password_hash'    => [
				'type'       => 'varchar',
				'constraint' => 255,
			],
			'reset_hash'       => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'reset_at'         => [
				'type' => 'datetime',
				'null' => true,
			],
			'reset_expires'    => [
				'type' => 'datetime',
				'null' => true,
			],
			'activate_hash'    => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'status'           => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'status_message'   => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'active'           => [
				'type'       => 'tinyint',
				'constraint' => 1,
				'null'       => 0,
				'default'    => 0,
			],
			'force_pass_reset' => [
				'type'       => 'tinyint',
				'constraint' => 1,
				'null'       => 0,
				'default'    => 0,
			],
			'created_at'       => [
				'type' => 'datetime',
				'null' => true,
			],
			'updated_at'       => [
				'type' => 'datetime',
				'null' => true,
			],
			'deleted_at'       => [
				'type' => 'datetime',
				'null' => true,
			],
		]);

		$this->forge->addKey('id', true);
		$this->forge->addUniqueKey('email');
		$this->forge->addUniqueKey('username');

		$this->forge->createTable('users', true);

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
		$this->forge->addKey('id', true);
		$this->forge->addKey('email');
		$this->forge->addKey('user_id');
		// NOTE: Do NOT delete the user_id or email when the user is deleted for security audits
		$this->forge->createTable('auth_logins', true);

		/*
		 * Auth Tokens
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
		]);
		$this->forge->addKey('id', true);
		$this->forge->addKey('selector');
		$this->forge->addForeignKey('user_id', 'users', 'id', false, 'CASCADE');
		$this->forge->createTable('auth_tokens', true);

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
		$this->forge->addKey('id', true);
		$this->forge->createTable('auth_reset_attempts');

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
		$this->forge->addKey('id', true);
		$this->forge->createTable('auth_activation_attempts');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		// drop constraints first to prevent errors
		if ($this->db->DBDriver !== 'SQLite3')
		{
			$this->forge->dropForeignKey('auth_tokens', 'auth_tokens_user_id_foreign');
		}

		$this->forge->dropTable('users', true);
		$this->forge->dropTable('auth_logins', true);
		$this->forge->dropTable('auth_tokens', true);
		$this->forge->dropTable('auth_reset_attempts', true);
		$this->forge->dropTable('auth_activation_attempts', true);
	}
}
