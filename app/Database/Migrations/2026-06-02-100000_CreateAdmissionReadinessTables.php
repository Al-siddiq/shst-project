<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds only the Phase 0 admissions readiness records.
 *
 * Application-domain tables deliberately wait for later phases. Documents can
 * be staged against an applicant profile before an application row exists.
 */
class CreateAdmissionReadinessTables extends Migration
{
    public function up()
    {
        // Block 1 access services already use these fields. Add them here as a
        // forward migration so existing installations gain the same staff
        // membership lifecycle metadata as fresh Phase 0 installations.
        $this->forge->addColumn('tenant_memberships', [
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active', 'after' => 'user_id'],
            'membership_label' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'status'],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'phone_e164' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'user_id'], 'uq_applicant_profile_tenant_user');
        $this->forge->addKey(['tenant_id', 'phone_e164'], false, false, 'idx_applicant_profile_phone');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('applicant_profiles', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'applicant_profile_id' => ['type' => 'INT', 'unsigned' => true],
            // Phase 3 attaches the document to a concrete application.
            'application_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'public_token' => ['type' => 'VARCHAR', 'constraint' => 64],
            'document_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'storage_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 120],
            'extension' => ['type' => 'VARCHAR', 'constraint' => 12],
            'size_bytes' => ['type' => 'INT', 'unsigned' => true],
            'checksum_sha256' => ['type' => 'VARCHAR', 'constraint' => 64],
            'review_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pending'],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'public_token'], 'uq_application_document_token');
        $this->forge->addUniqueKey(['tenant_id', 'storage_path'], 'uq_application_document_path');
        $this->forge->addKey(['tenant_id', 'applicant_profile_id', 'review_status'], false, false, 'idx_application_document_owner');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('applicant_profile_id', 'applicant_profiles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('application_documents', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'namespace' => ['type' => 'VARCHAR', 'constraint' => 80],
            'next_value' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'namespace'], 'uq_admission_reference_namespace');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_reference_sequences', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_reference_sequences', true);
        $this->forge->dropTable('application_documents', true);
        $this->forge->dropTable('applicant_profiles', true);
        $this->forge->dropColumn('tenant_memberships', ['membership_label', 'status']);
    }
}
