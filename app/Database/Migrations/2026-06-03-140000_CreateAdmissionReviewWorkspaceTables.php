<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds Phase 4 tenant-staff review, correction, document, and screening records. */
class CreateAdmissionReviewWorkspaceTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'review_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'in_review'],
            'reviewer_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'private_notes' => ['type' => 'TEXT', 'null' => true],
            'public_correction_message' => ['type' => 'TEXT', 'null' => true],
            'correction_allowed_until' => ['type' => 'DATETIME', 'null' => true],
            'reviewed_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'applicant_application_id', 'review_status'], false, false, 'idx_admission_application_review_status');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_application_reviews', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'application_document_id' => ['type' => 'INT', 'unsigned' => true],
            'decision' => ['type' => 'VARCHAR', 'constraint' => 40],
            'reviewer_user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'private_notes' => ['type' => 'TEXT', 'null' => true],
            'applicant_message' => ['type' => 'TEXT', 'null' => true],
            'reviewed_at' => ['type' => 'DATETIME'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'application_document_id', 'decision'], false, false, 'idx_document_review_decision');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('application_document_id', 'application_documents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_document_review_logs', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'screening_type' => ['type' => 'VARCHAR', 'constraint' => 60, 'default' => 'manual_review'],
            'scheduled_at' => ['type' => 'DATETIME', 'null' => true],
            'held_at' => ['type' => 'DATETIME', 'null' => true],
            'venue' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'score' => ['type' => 'DECIMAL', 'constraint' => '7,2', 'null' => true],
            'outcome' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'pending'],
            'private_notes' => ['type' => 'TEXT', 'null' => true],
            'decided_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'applicant_application_id', 'outcome'], false, false, 'idx_admission_screening_outcome');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_screening_records', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_screening_records', true);
        $this->forge->dropTable('admission_document_review_logs', true);
        $this->forge->dropTable('admission_application_reviews', true);
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
