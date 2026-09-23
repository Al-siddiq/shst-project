<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/** Phase 1 permanent tenant bindings and controlled platform support contexts. */
class HardenIdentityTenantBoundary extends Migration
{
    public function up(): void
    {
        $membershipTable = $this->db->prefixTable('tenant_memberships');
        $duplicates = $this->db->query(
            'SELECT user_id, COUNT(*) AS membership_count FROM ' . $membershipTable . ' GROUP BY user_id HAVING COUNT(*) > 1 LIMIT 20'
        )->getResultArray();
        if ($duplicates !== []) {
            throw new RuntimeException(
                'Phase 1 preflight failed: Shield users with multiple tenant memberships must be remediated before migration. Conflicts: '
                . json_encode($duplicates, JSON_UNESCAPED_SLASHES)
            );
        }

        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query('CREATE UNIQUE INDEX uq_tenant_membership_user ON ' . $membershipTable . ' (user_id)');
        } else {
            $this->db->query('ALTER TABLE ' . $membershipTable . ' ADD UNIQUE KEY uq_tenant_membership_user (user_id)');
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_membership_id' => ['type' => 'INT', 'unsigned' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'from_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'to_status' => ['type' => 'VARCHAR', 'constraint' => 30],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'changed_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'user_id', 'created_at'], false, false, 'idx_membership_history_lookup');
        $this->forge->addForeignKey('tenant_membership_id', 'tenant_memberships', 'id', 'RESTRICT', 'CASCADE', 'fk_membership_history_membership');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'RESTRICT', 'CASCADE', 'fk_membership_history_tenant');
        $this->forge->createTable('tenant_membership_history', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'public_token' => ['type' => 'CHAR', 'constraint' => 48],
            'platform_user_id' => ['type' => 'INT', 'unsigned' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500],
            'access_mode' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'read_only'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'started_at' => ['type' => 'DATETIME'],
            'expires_at' => ['type' => 'DATETIME'],
            'ended_at' => ['type' => 'DATETIME', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('public_token', 'uq_platform_support_public_token');
        $this->forge->addKey(['platform_user_id', 'status', 'expires_at'], false, false, 'idx_platform_support_actor');
        $this->forge->addKey(['tenant_id', 'status', 'expires_at'], false, false, 'idx_platform_support_tenant');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'RESTRICT', 'CASCADE', 'fk_platform_support_tenant');
        $this->forge->createTable('platform_support_contexts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('platform_support_contexts', true);
        $this->forge->dropTable('tenant_membership_history', true);
        if ($this->db->DBDriver === 'SQLite3') {
            $this->db->query('DROP INDEX uq_tenant_membership_user');
        } else {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('tenant_memberships') . ' DROP INDEX uq_tenant_membership_user');
        }
    }
}
