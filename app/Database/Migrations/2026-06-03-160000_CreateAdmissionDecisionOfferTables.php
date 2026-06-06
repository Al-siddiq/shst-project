<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds Phase 5 shortlisting, decision, and offer records; publication waits for Phase 6. */
class CreateAdmissionDecisionOfferTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->auditedFields([
            'admission_cycle_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_opening_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'batch_code' => ['type' => 'VARCHAR', 'constraint' => 80],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'batch_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'shortlist'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'draft'],
            'private_notes' => ['type' => 'TEXT', 'null' => true],
            'finalized_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'batch_code'], 'uq_admission_shortlist_batch_code');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_cycle_id', 'admission_cycles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_opening_id', 'admission_programme_openings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_shortlist_batches', true);

        $this->forge->addField($this->auditedFields([
            'shortlist_batch_id' => ['type' => 'INT', 'unsigned' => true],
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'entry_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'shortlisted'],
            'rank_position' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'private_notes' => ['type' => 'TEXT', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'shortlist_batch_id', 'applicant_application_id'], 'uq_admission_shortlist_entry');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('shortlist_batch_id', 'admission_shortlist_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_shortlist_entries', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'decision_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'decision_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'draft'],
            'is_current' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'requires_approval' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'decided_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'decision_reason' => ['type' => 'TEXT', 'null' => true],
            'private_notes' => ['type' => 'TEXT', 'null' => true],
            'decided_at' => ['type' => 'DATETIME'],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'applicant_application_id', 'is_current'], false, false, 'idx_admission_decision_current');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_decisions', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'admission_decision_id' => ['type' => 'INT', 'unsigned' => true],
            'offer_reference' => ['type' => 'VARCHAR', 'constraint' => 80],
            'offer_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'issued'],
            'offered_programme_opening_id' => ['type' => 'INT', 'unsigned' => true],
            'offer_snapshot_json' => ['type' => 'LONGTEXT'],
            'offer_letter_template_key' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'issued_at' => ['type' => 'DATETIME'],
            'expires_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'offer_reference'], 'uq_admission_offer_reference');
        $this->forge->addKey(['tenant_id', 'applicant_application_id', 'offer_status'], false, false, 'idx_admission_offer_active');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_decision_id', 'admission_decisions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('offered_programme_opening_id', 'admission_programme_openings', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('admission_offers', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_offers', true);
        $this->forge->dropTable('admission_decisions', true);
        $this->forge->dropTable('admission_shortlist_entries', true);
        $this->forge->dropTable('admission_shortlist_batches', true);
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
