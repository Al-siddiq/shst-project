<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Operational delivery, scan claiming, and applicant notification visibility. */
class AddBlockThreeOperations extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('application_documents', [
            'scan_locked_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'scan_status'],
            'scan_locked_by' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'scan_locked_at'],
        ]);
        $this->forge->addColumn('admission_notification_outbox', [
            'provider_message_id' => ['type'=>'VARCHAR','constraint'=>190,'null'=>true,'after'=>'sent_at'],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'event_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'title' => ['type' => 'VARCHAR', 'constraint' => 200],
            'body' => ['type' => 'TEXT'],
            'action_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'read_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['tenant_id', 'user_id', 'read_at', 'created_at'], false, false, 'idx_in_app_recipient');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'RESTRICT', 'CASCADE', 'fk_in_app_tenant');
        $this->forge->createTable('in_app_notifications', true);

        if ($this->db->DBDriver === 'SQLite3') {
            $table = $this->db->prefixTable('application_documents');
            $this->db->query("CREATE INDEX idx_document_scan_claim ON {$table} (scan_status, scan_locked_at, created_at)");
        } else {
            $this->db->query('ALTER TABLE ' . $this->db->prefixTable('application_documents') . ' ADD KEY idx_document_scan_claim (scan_status, scan_locked_at, created_at)');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('in_app_notifications', true);
        if ($this->db->DBDriver === 'SQLite3') $this->db->query('DROP INDEX IF EXISTS idx_document_scan_claim');
        else $this->db->query('ALTER TABLE ' . $this->db->prefixTable('application_documents') . ' DROP INDEX idx_document_scan_claim');
        $this->forge->dropColumn('application_documents', ['scan_locked_by', 'scan_locked_at']);
        $this->forge->dropColumn('admission_notification_outbox', 'provider_message_id');
    }
}
