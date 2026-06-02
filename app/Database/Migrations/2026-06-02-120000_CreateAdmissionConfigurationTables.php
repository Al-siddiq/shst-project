<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Creates tenant-owned Phase 1 admission configuration records only. */
class CreateAdmissionConfigurationTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->auditedFields([
            'academic_session_id' => ['type' => 'INT', 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'code' => ['type' => 'VARCHAR', 'constraint' => 60],
            'opens_at' => ['type' => 'DATETIME'],
            'closes_at' => ['type' => 'DATETIME'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'is_public' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'allow_multiple_public_cycles' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'instructions' => ['type' => 'TEXT', 'null' => true],
            'screening_instructions' => ['type' => 'TEXT', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'code'], 'uq_admission_cycle_code');
        $this->forge->addKey(['tenant_id', 'status', 'is_public', 'opens_at', 'closes_at'], false, false, 'idx_admission_cycle_public');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('academic_session_id', 'academic_sessions', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('admission_cycles', true);

        $this->forge->addField($this->auditedFields([
            'admission_cycle_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_id' => ['type' => 'INT', 'unsigned' => true],
            'department_id' => ['type' => 'INT', 'unsigned' => true],
            'entry_level_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'application_quota' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'screening_method' => ['type' => 'VARCHAR', 'constraint' => 80, 'default' => 'manual_review'],
            'instructions' => ['type' => 'TEXT', 'null' => true],
            'requirement_summary' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'open'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'admission_cycle_id', 'programme_id', 'entry_level_id'], 'uq_admission_programme_opening');
        $this->forge->addKey(['tenant_id', 'admission_cycle_id', 'status', 'sort_order'], false, false, 'idx_admission_programme_public');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_cycle_id', 'admission_cycles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_id', 'programmes', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('entry_level_id', 'levels', 'id', 'SET NULL', 'RESTRICT');
        $this->forge->createTable('admission_programme_openings', true);

        $this->forge->addField($this->auditedFields([
            'admission_cycle_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_opening_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'requirement_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'code' => ['type' => 'VARCHAR', 'constraint' => 80],
            'label' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'configuration_json' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'requirement_type', 'code'], 'uq_admission_requirement_definition');
        $this->forge->addKey(['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'status', 'sort_order'], false, false, 'idx_admission_requirement_scope');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_cycle_id', 'admission_cycles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_opening_id', 'admission_programme_openings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_requirement_definitions', true);

        $this->forge->addField($this->auditedFields([
            'admission_cycle_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_opening_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'subject_code' => ['type' => 'VARCHAR', 'constraint' => 60],
            'subject_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'minimum_grade' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'requirement_group' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'is_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'subject_code'], 'uq_admission_subject_requirement');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_cycle_id', 'admission_cycles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_opening_id', 'admission_programme_openings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_subject_requirements', true);

        $this->forge->addField($this->auditedFields([
            'admission_cycle_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_opening_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'label' => ['type' => 'VARCHAR', 'constraint' => 180],
            'allowed_mime_types' => ['type' => 'TEXT'],
            'maximum_size_bytes' => ['type' => 'INT', 'unsigned' => true],
            'is_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'document_type'], 'uq_admission_document_requirement');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_cycle_id', 'admission_cycles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_opening_id', 'admission_programme_openings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_document_requirements', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_document_requirements', true);
        $this->forge->dropTable('admission_subject_requirements', true);
        $this->forge->dropTable('admission_requirement_definitions', true);
        $this->forge->dropTable('admission_programme_openings', true);
        $this->forge->dropTable('admission_cycles', true);
    }

    /** @param array<string, array<string, mixed>> $specific */
    private function auditedFields(array $specific): array
    {
        return array_merge([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
        ], $specific, [
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }
}
