<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the tenant-owned media catalogue required before public content exists.
 *
 * Files remain outside executable public paths. This table stores only metadata
 * and generated storage keys so controllers never trust client filesystem paths.
 */
class CreateWebsiteMediaFoundation extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'stored_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'storage_disk' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'local'],
            'storage_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 120],
            'extension' => ['type' => 'VARCHAR', 'constraint' => 20],
            'size_bytes' => ['type' => 'BIGINT', 'unsigned' => true],
            'width' => ['type' => 'INT', 'unsigned' => true],
            'height' => ['type' => 'INT', 'unsigned' => true],
            'visibility' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'private'],
            'category' => ['type' => 'VARCHAR', 'constraint' => 80],
            'checksum_sha256' => ['type' => 'VARCHAR', 'constraint' => 64],
            'derivatives' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'updated_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'visibility', 'category'], false, false, 'idx_media_tenant_visibility_category');
        $this->forge->addUniqueKey(['tenant_id', 'storage_path'], 'uq_media_tenant_storage_path');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('media_files', true);
    }

    public function down()
    {
        $this->forge->dropTable('media_files', true);
    }
}
