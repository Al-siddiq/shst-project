<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds Phase 7 acceptance, clearance placeholder, and Block 5 handoff marker tables. */
class CreateAdmissionAcceptanceClearanceTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'admission_offer_id' => ['type' => 'INT', 'unsigned' => true],
            'acceptance_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'pending'],
            'acceptance_fee_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'not_required'],
            'accepted_at' => ['type' => 'DATETIME', 'null' => true],
            'declined_at' => ['type' => 'DATETIME', 'null' => true],
            'applicant_comment' => ['type' => 'TEXT', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'admission_offer_id'], 'uq_admission_offer_acceptance');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_offer_id', 'admission_offers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_offer_acceptances', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'admission_offer_id' => ['type' => 'INT', 'unsigned' => true],
            'clearance_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'pending'],
            'acceptance_fee_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'not_required'],
            'staff_notes' => ['type' => 'TEXT', 'null' => true],
            'updated_by_staff_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'cleared_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'applicant_application_id'], 'uq_admission_clearance_application');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_offer_id', 'admission_offers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_clearance_statuses', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'admission_offer_id' => ['type' => 'INT', 'unsigned' => true],
            'eligibility_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'eligible_for_student_conversion'],
            'marked_at' => ['type' => 'DATETIME'],
            'handoff_payload_json' => ['type' => 'LONGTEXT'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'applicant_application_id'], 'uq_admission_conversion_eligibility');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_offer_id', 'admission_offers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_conversion_eligibility_markers', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_conversion_eligibility_markers', true);
        $this->forge->dropTable('admission_clearance_statuses', true);
        $this->forge->dropTable('admission_offer_acceptances', true);
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
