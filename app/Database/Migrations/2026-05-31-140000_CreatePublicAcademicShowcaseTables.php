<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds public-facing extensions for Block 1 academic records and admissions.
 *
 * Academic configuration remains the operational source of truth. These tables
 * store optional public copy and publication state so internal records can exist
 * without being exposed on a tenant website.
 */
class CreatePublicAcademicShowcaseTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->profileFields([
            'department_id' => ['type' => 'INT', 'unsigned' => true],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 180],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'body' => ['type' => 'TEXT', 'null' => true],
            'featured_media_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'seo_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'department_id'], 'uq_department_public_profile');
        $this->forge->addUniqueKey(['tenant_id', 'slug'], 'uq_department_public_slug');
        $this->forge->addKey(['tenant_id', 'status'], false, false, 'idx_department_public_status');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('featured_media_id', 'media_files', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('department_public_profiles', true);

        $this->forge->addField($this->profileFields([
            'programme_id' => ['type' => 'INT', 'unsigned' => true],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 180],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'body' => ['type' => 'TEXT', 'null' => true],
            'entry_requirements' => ['type' => 'TEXT', 'null' => true],
            'career_opportunities' => ['type' => 'TEXT', 'null' => true],
            'duration_explanation' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'award_type' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'admission_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'not_specified'],
            'featured_media_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'seo_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'programme_id'], 'uq_programme_public_profile');
        $this->forge->addUniqueKey(['tenant_id', 'slug'], 'uq_programme_public_slug');
        $this->forge->addKey(['tenant_id', 'status'], false, false, 'idx_programme_public_status');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('programme_id', 'programmes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('featured_media_id', 'media_files', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('programme_public_profiles', true);

        $this->forge->addField($this->profileFields([
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 180, 'default' => 'admissions'],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'body' => ['type' => 'TEXT', 'null' => true],
            'admission_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'closed'],
            'requirements_body' => ['type' => 'TEXT', 'null' => true],
            'application_fee_note' => ['type' => 'TEXT', 'null' => true],
            'screening_information' => ['type' => 'TEXT', 'null' => true],
            'required_documents' => ['type' => 'TEXT', 'null' => true],
            'important_dates' => ['type' => 'TEXT', 'null' => true],
            'how_to_apply_body' => ['type' => 'TEXT', 'null' => true],
            'application_link_label' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'application_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'seo_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('tenant_id', 'uq_admission_information_tenant');
        $this->forge->addUniqueKey(['tenant_id', 'slug'], 'uq_admission_information_slug');
        $this->forge->addKey(['tenant_id', 'status'], false, false, 'idx_admission_information_status');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('admission_information_pages', true);
    }

    public function down()
    {
        $this->forge->dropTable('admission_information_pages', true);
        $this->forge->dropTable('programme_public_profiles', true);
        $this->forge->dropTable('department_public_profiles', true);
    }

    /**
     * Shared lifecycle and attribution fields keep showcase tables consistent.
     *
     * @param array<string, array<string, mixed>> $specific
     * @return array<string, array<string, mixed>>
     */
    private function profileFields(array $specific): array
    {
        return array_merge([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
        ], $specific, [
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'published_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }
}
