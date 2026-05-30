<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantThemeAndAuditTables extends Migration
{
    public function up()
    {
        // Tenant-owned theme settings. Values are data-driven per school, not hard-coded.
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'primary_color' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#0f766e'],
            'secondary_color' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#134e4a'],
            'accent_color' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#f59e0b'],
            'logo_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('tenant_id');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tenant_themes', true);

        // Department color identities are separated from academic departments for UI branding.
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'department_id' => ['type' => 'INT', 'unsigned' => true],
            'color_hex' => ['type' => 'VARCHAR', 'constraint' => 10],
            'icon_key' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'department_id'], 'uq_department_identity');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('department_identities', true);

        // Unified audit log for platform and tenant configuration/access activity.
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'actor_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'context' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'tenant'],
            'action' => ['type' => 'VARCHAR', 'constraint' => 120],
            'target_type' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'target_id' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'summary' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'metadata' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'action']);
        $this->forge->createTable('audit_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('department_identities', true);
        $this->forge->dropTable('tenant_themes', true);
    }
}
