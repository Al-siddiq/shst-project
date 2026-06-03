<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds Phase 3 completion records: neutral O'Level references, applicant-owned
 * O'Level rows, immutable submission snapshots, and notification outbox events.
 */
class CreateApplicantSubmissionTables extends Migration
{
    public function up()
    {
        $this->forge->addColumn('applicant_applications', [
            'application_number' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'public_token'],
            'submitted_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'last_saved_at'],
            'submission_snapshot_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'submitted_at'],
        ]);
        $this->db->query('CREATE UNIQUE INDEX uq_applicant_application_number ON applicant_applications (tenant_id, application_number)');

        $this->forge->addField([
            'code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'label' => ['type' => 'VARCHAR', 'constraint' => 120],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('code', true);
        $this->forge->createTable('olevel_exam_types', true);

        $this->forge->addField([
            'code' => ['type' => 'VARCHAR', 'constraint' => 60],
            'label' => ['type' => 'VARCHAR', 'constraint' => 160],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('code', true);
        $this->forge->createTable('olevel_subjects', true);

        $this->forge->addField([
            'code' => ['type' => 'VARCHAR', 'constraint' => 20],
            'label' => ['type' => 'VARCHAR', 'constraint' => 80],
            'rank_value' => ['type' => 'INT', 'default' => 0],
            'is_passing' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('code', true);
        $this->forge->createTable('olevel_grades', true);

        $this->db->table('olevel_exam_types')->insertBatch([
            ['code' => 'WAEC', 'label' => 'WAEC Senior School Certificate Examination', 'sort_order' => 10],
            ['code' => 'NECO', 'label' => 'NECO Senior School Certificate Examination', 'sort_order' => 20],
            ['code' => 'NABTEB', 'label' => 'NABTEB Certificate Examination', 'sort_order' => 30],
        ]);
        $this->db->table('olevel_subjects')->insertBatch([
            ['code' => 'ENG', 'label' => 'English Language', 'sort_order' => 10],
            ['code' => 'MTH', 'label' => 'Mathematics', 'sort_order' => 20],
            ['code' => 'BIO', 'label' => 'Biology', 'sort_order' => 30],
            ['code' => 'CHE', 'label' => 'Chemistry', 'sort_order' => 40],
            ['code' => 'PHY', 'label' => 'Physics', 'sort_order' => 50],
        ]);
        $this->db->table('olevel_grades')->insertBatch([
            ['code' => 'A1', 'label' => 'Excellent', 'rank_value' => 1, 'sort_order' => 10],
            ['code' => 'B2', 'label' => 'Very Good', 'rank_value' => 2, 'sort_order' => 20],
            ['code' => 'B3', 'label' => 'Good', 'rank_value' => 3, 'sort_order' => 30],
            ['code' => 'C4', 'label' => 'Credit', 'rank_value' => 4, 'sort_order' => 40],
            ['code' => 'C5', 'label' => 'Credit', 'rank_value' => 5, 'sort_order' => 50],
            ['code' => 'C6', 'label' => 'Credit', 'rank_value' => 6, 'sort_order' => 60],
            ['code' => 'D7', 'label' => 'Pass', 'rank_value' => 7, 'is_passing' => 0, 'sort_order' => 70],
            ['code' => 'E8', 'label' => 'Pass', 'rank_value' => 8, 'is_passing' => 0, 'sort_order' => 80],
            ['code' => 'F9', 'label' => 'Fail', 'rank_value' => 9, 'is_passing' => 0, 'sort_order' => 90],
        ]);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'exam_type_code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'exam_year' => ['type' => 'INT', 'unsigned' => true],
            'exam_number' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'sitting_label' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'applicant_application_id'], false, false, 'idx_olevel_sitting_application');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('application_olevel_sittings', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'olevel_sitting_id' => ['type' => 'INT', 'unsigned' => true],
            'subject_code' => ['type' => 'VARCHAR', 'constraint' => 60],
            'subject_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'grade_code' => ['type' => 'VARCHAR', 'constraint' => 20],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'olevel_sitting_id', 'subject_code'], 'uq_olevel_sitting_subject');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('olevel_sitting_id', 'application_olevel_sittings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('application_olevel_results', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'application_number' => ['type' => 'VARCHAR', 'constraint' => 80],
            'snapshot_json' => ['type' => 'LONGTEXT'],
            'submitted_at' => ['type' => 'DATETIME'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'applicant_application_id'], 'uq_application_submission_snapshot');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('application_submission_snapshots', true);

        $this->forge->addField($this->auditedFields([
            'event_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'channel' => ['type' => 'VARCHAR', 'constraint' => 30],
            'recipient' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'payload_json' => ['type' => 'LONGTEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
            'available_at' => ['type' => 'DATETIME', 'null' => true],
            'attempts' => ['type' => 'INT', 'default' => 0],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'event_name', 'status'], false, false, 'idx_admission_notification_event');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_notification_outbox', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_notification_outbox', true);
        $this->forge->dropTable('application_submission_snapshots', true);
        $this->forge->dropTable('application_olevel_results', true);
        $this->forge->dropTable('application_olevel_sittings', true);
        $this->forge->dropTable('olevel_grades', true);
        $this->forge->dropTable('olevel_subjects', true);
        $this->forge->dropTable('olevel_exam_types', true);
        $this->forge->dropColumn('applicant_applications', ['submission_snapshot_id', 'submitted_at', 'application_number']);
    }

    /** @param array<string, array<string, mixed>> $specific */
    private function auditedFields(array $specific): array
    {
        return array_merge(['id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true], 'tenant_id' => ['type' => 'INT', 'unsigned' => true]], $specific, [
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }
}
