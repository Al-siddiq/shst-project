<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds Phase 6 versioned admission-list publications and safe public entries. */
class CreateAdmissionListPublicationTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->auditedFields([
            'admission_cycle_id' => ['type' => 'INT', 'unsigned' => true],
            'programme_opening_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'version_number' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'public_token' => ['type' => 'VARCHAR', 'constraint' => 80],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'draft'],
            'safe_fields_json' => ['type' => 'TEXT', 'null' => true],
            'export_hook' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'private_notes' => ['type' => 'TEXT', 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'published_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'public_token'], 'uq_admission_list_public_token');
        $this->forge->addUniqueKey(['tenant_id', 'admission_cycle_id', 'version_number'], 'uq_admission_list_cycle_version');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_cycle_id', 'admission_cycles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_opening_id', 'admission_programme_openings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_list_publications', true);

        $this->forge->addField($this->auditedFields([
            'admission_list_publication_id' => ['type' => 'INT', 'unsigned' => true],
            'applicant_application_id' => ['type' => 'INT', 'unsigned' => true],
            'admission_offer_id' => ['type' => 'INT', 'unsigned' => true],
            'application_number' => ['type' => 'VARCHAR', 'constraint' => 80],
            'applicant_display_name' => ['type' => 'VARCHAR', 'constraint' => 220],
            'programme_name' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'entry_position' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'entry_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'published'],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'admission_list_publication_id', 'applicant_application_id'], 'uq_admission_list_entry_application');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_list_publication_id', 'admission_list_publications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_application_id', 'applicant_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admission_offer_id', 'admission_offers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_list_entries', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_list_entries', true);
        $this->forge->dropTable('admission_list_publications', true);
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
