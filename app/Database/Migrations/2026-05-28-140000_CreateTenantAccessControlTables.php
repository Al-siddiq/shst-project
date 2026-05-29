<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantAccessControlTables extends Migration
{
    public function up()
    {
        // Extends membership beyond tenant resolution so Phase 4 can track access state.
        $this->forge->addColumn('tenant_memberships', [
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active', 'after' => 'user_id'],
            'membership_label' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'status'],
        ]);

        // Tenant-scoped IAM group assignment bridge. Shield remains identity foundation;
        // this table scopes broad groups per tenant without mixing in operational positions.
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'group_name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'user_id', 'group_name'], 'uq_tenant_user_group');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tenant_iam_group_assignments', true);

        // Tenant-configured operational authorities such as registrar, HOD, bursar, etc.
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 120],
            'name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'code'], 'uq_tenant_authority_code');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('operational_authorities', true);

        // Grants operational authorities to tenant members, optionally scoped for later modules.
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'authority_id' => ['type' => 'INT', 'unsigned' => true],
            'scope_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'scope_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'user_id', 'authority_id', 'scope_type', 'scope_id'], 'uq_tenant_authority_grant');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('authority_id', 'operational_authorities', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('membership_authorities', true);
    }

    public function down()
    {
        $this->forge->dropTable('membership_authorities', true);
        $this->forge->dropTable('operational_authorities', true);
        $this->forge->dropTable('tenant_iam_group_assignments', true);
        $this->forge->dropColumn('tenant_memberships', ['status', 'membership_label']);
    }
}
