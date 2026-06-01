<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the shared editorial table for news, announcements, and calendar notices.
 *
 * These content types share publication mechanics but retain type-specific
 * metadata. One tenant-scoped table keeps lifecycle queries and audit behavior
 * predictable without turning the website into an unrestricted page builder.
 */
class CreateWebsiteEditorialContentTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'content_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 180],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'body' => ['type' => 'TEXT'],
            'featured_media_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'related_department_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'related_programme_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'author_display_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'announcement_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'priority' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'normal'],
            'audience' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'public'],
            'event_start_at' => ['type' => 'DATETIME', 'null' => true],
            'event_end_at' => ['type' => 'DATETIME', 'null' => true],
            'academic_session_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'semester_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'visibility' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'public'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'scheduled_for' => ['type' => 'DATETIME', 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'archived_at' => ['type' => 'DATETIME', 'null' => true],
            'published_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'seo_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'content_type', 'slug'], 'uq_website_content_type_slug');
        $this->forge->addKey(['tenant_id', 'content_type', 'status', 'scheduled_for'], false, false, 'idx_website_content_publication');
        $this->forge->addKey(['tenant_id', 'content_type', 'event_start_at'], false, false, 'idx_website_content_event_start');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('featured_media_id', 'media_files', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('related_department_id', 'departments', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('related_programme_id', 'programmes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('academic_session_id', 'academic_sessions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('semester_id', 'semesters', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('website_content_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('website_content_items', true);
    }
}
