<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Adds standalone public leadership and ordered gallery records for Phase 4. */
class CreateInstitutionalShowcaseTables extends Migration
{
    public function up()
    {
        $this->forge->addField($this->publishableFields([
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'bio' => ['type' => 'TEXT'],
            'photo_media_id' => ['type' => 'INT', 'unsigned' => true],
            'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'seo_title' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'seo_description' => ['type' => 'VARCHAR', 'constraint' => 320, 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'status', 'sort_order'], false, false, 'idx_management_public');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('photo_media_id', 'media_files', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('management_profiles', true);

        $this->forge->addField($this->publishableFields([
            'title' => ['type' => 'VARCHAR', 'constraint' => 180],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT'],
            'category' => ['type' => 'VARCHAR', 'constraint' => 120],
            'cover_media_id' => ['type' => 'INT', 'unsigned' => true],
            'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'slug'], 'uq_gallery_album_slug');
        $this->forge->addKey(['tenant_id', 'status', 'sort_order'], false, false, 'idx_gallery_album_public');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('cover_media_id', 'media_files', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('gallery_albums', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'gallery_album_id' => ['type' => 'INT', 'unsigned' => true],
            'media_file_id' => ['type' => 'INT', 'unsigned' => true],
            'caption' => ['type' => 'VARCHAR', 'constraint' => 500],
            'alt_text' => ['type' => 'VARCHAR', 'constraint' => 255],
            'sort_order' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'published'],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'gallery_album_id', 'media_file_id'], 'uq_gallery_album_media');
        $this->forge->addKey(['tenant_id', 'gallery_album_id', 'status', 'sort_order'], false, false, 'idx_gallery_item_public');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('gallery_album_id', 'gallery_albums', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('media_file_id', 'media_files', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('gallery_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('gallery_items', true);
        $this->forge->dropTable('gallery_albums', true);
        $this->forge->dropTable('management_profiles', true);
    }

    /** @param array<string, array<string, mixed>> $specific */
    private function publishableFields(array $specific): array
    {
        return array_merge(['id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true], 'tenant_id' => ['type' => 'INT', 'unsigned' => true]], $specific, [
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'published_at' => ['type' => 'DATETIME', 'null' => true], 'published_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'archived_at' => ['type' => 'DATETIME', 'null' => true], 'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true], 'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true], 'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }
}
