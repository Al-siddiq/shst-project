<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantConfigurationTables extends Migration
{
    public function up()
    {
        // Tenant-owned school profile.
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'institution_name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'short_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'official_email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'official_phone' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'state' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'lga' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('tenant_id');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tenant_profiles', true);

        $this->createAcademicTable('academic_sessions', [
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'start_date' => ['type' => 'DATE', 'null' => true],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'is_current' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
        ], ['tenant_id', 'name']);

        $this->createAcademicTable('semesters', [
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'code' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], ['tenant_id', 'name']);

        $this->createAcademicTable('levels', [
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'code' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], ['tenant_id', 'name']);

        $this->createAcademicTable('departments', [
            'name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'code' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'color_hex' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], ['tenant_id', 'name']);

        $this->createAcademicTable('programmes', [
            'department_id' => ['type' => 'INT', 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 200],
            'code' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'duration_years' => ['type' => 'INT', 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], ['tenant_id', 'name'], [
            ['department_id', 'departments', 'id'],
        ]);

        $this->createAcademicTable('courses', [
            'department_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 200],
            'course_code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'credit_units' => ['type' => 'INT', 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ], ['tenant_id', 'course_code'], [
            ['department_id', 'departments', 'id'],
        ]);

        // Course-to-programme mapping.
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_id' => ['type' => 'INT', 'unsigned' => true],
            'course_id' => ['type' => 'INT', 'unsigned' => true],
            'level_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'semester_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'is_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'programme_id', 'course_id', 'level_id', 'semester_id'], 'uq_programme_courses');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_id', 'programmes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('level_id', 'levels', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('semester_id', 'semesters', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('programme_courses', true);
    }

    private function createAcademicTable(string $table, array $fields, array $unique, array $fks = []): void
    {
        $base = [
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ];

        $this->forge->addField(array_merge($base, $fields));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey($unique);
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');

        foreach ($fks as [$column, $refTable, $refColumn]) {
            $this->forge->addForeignKey($column, $refTable, $refColumn, 'CASCADE', 'CASCADE');
        }

        $this->forge->createTable($table, true);
    }

    public function down()
    {
        $this->forge->dropTable('programme_courses', true);
        $this->forge->dropTable('courses', true);
        $this->forge->dropTable('programmes', true);
        $this->forge->dropTable('departments', true);
        $this->forge->dropTable('levels', true);
        $this->forge->dropTable('semesters', true);
        $this->forge->dropTable('academic_sessions', true);
        $this->forge->dropTable('tenant_profiles', true);
    }
}
