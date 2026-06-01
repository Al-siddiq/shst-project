<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the Phase 1 public-site settings and navigation sources.
 *
 * Both tables are tenant-owned because even apparently simple values such as a
 * school tagline or menu label vary between institutions and must never become
 * code-level assumptions in a shared SaaS application.
 */
class CreatePublicWebsiteCoreTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'site_title' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'tagline' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'motto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'hero_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'hero_summary' => ['type' => 'TEXT', 'null' => true],
            'hero_media_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'about_summary' => ['type' => 'TEXT', 'null' => true],
            'about_body' => ['type' => 'TEXT', 'null' => true],
            'mission' => ['type' => 'TEXT', 'null' => true],
            'vision' => ['type' => 'TEXT', 'null' => true],
            'history' => ['type' => 'TEXT', 'null' => true],
            'contact_email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'contact_phone' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'contact_phone_alt' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'map_embed_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'portal_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'application_info_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'application_cta_label' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'is_public_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'seo_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
            'facebook_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'instagram_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'x_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'youtube_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('tenant_id', 'uq_website_settings_tenant');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('hero_media_id', 'media_files', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('website_settings', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'parent_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'label' => ['type' => 'VARCHAR', 'constraint' => 120],
            'link_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'route'],
            'route_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'content_slug' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'target' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '_self'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'is_visible' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'status', 'is_visible', 'sort_order'], false, false, 'idx_website_menu_public_order');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'website_menu_items', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('website_menu_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('website_menu_items', true);
        $this->forge->dropTable('website_settings', true);
    }
}
