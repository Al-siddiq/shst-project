<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds Phase 2 applicant draft records only; submission tables arrive later. */
class CreateApplicantDraftApplicationTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->auditedFields([
            'applicant_profile_id' => ['type' => 'INT', 'unsigned' => true],
            'admission_cycle_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_opening_id' => ['type' => 'INT', 'unsigned' => true],
            'public_token' => ['type' => 'VARCHAR', 'constraint' => 64],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'biodata_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'not_started'],
            'started_at' => ['type' => 'DATETIME', 'null' => true],
            'last_saved_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'public_token'], 'uq_applicant_application_token');
        // Service-level idempotency resumes existing drafts, but duplicate-control
        // policy is configurable later so this remains an index, not a unique key.
        $this->forge->addKey(['tenant_id', 'applicant_profile_id', 'admission_cycle_id', 'programme_opening_id'], false, false, 'idx_applicant_programme_draft');
        $this->forge->addKey(['tenant_id', 'applicant_profile_id', 'status'], false, false, 'idx_applicant_application_owner');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_profile_id', 'applicant_profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_cycle_id', 'admission_cycles', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('programme_opening_id', 'admission_programme_openings', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('applicant_applications', true);

        $this->forge->addField($this->auditedFields([
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'surname' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'other_names' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'gender' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'date_of_birth' => ['type' => 'DATE', 'null' => true],
            'phone_e164' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'residential_address' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'state_of_origin' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'lga_of_origin' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'nationality' => ['type' => 'VARCHAR', 'constraint' => 120, 'default' => 'Nigerian'],
            'marital_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'religion' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'next_of_kin_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'next_of_kin_phone_e164' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'guardian_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'guardian_phone_e164' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'completion_percent' => ['type' => 'INT', 'default' => 0],
            'last_saved_at' => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'applicant_application_id'], 'uq_application_biodata_draft');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('application_biodata_drafts', true);
    }

    public function down()
    {
        $this->forge->dropTable('application_biodata_drafts', true);
        $this->forge->dropTable('applicant_applications', true);
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
