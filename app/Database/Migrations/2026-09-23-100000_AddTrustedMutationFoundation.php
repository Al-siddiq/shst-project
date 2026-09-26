<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/** Phase 2 integrity, idempotency, immutable snapshot, file, and outbox contract. */
class AddTrustedMutationFoundation extends Migration
{
    public function up(): void
    {
        $this->assertNoTenantMismatch();

        $this->forge->addColumn('applicant_applications', [
            'lock_version' => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'after' => 'status'],
            'submission_version' => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'after' => 'submission_snapshot_id'],
        ]);
        $this->forge->addColumn('application_submission_snapshots', [
            'submission_version' => ['type' => 'INT', 'unsigned' => true, 'default' => 1, 'after' => 'application_number'],
        ]);
        $this->forge->addColumn('application_documents', [
            'storage_state' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'legacy_unverified', 'after' => 'checksum_sha256'],
            'scan_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'not_scanned', 'after' => 'storage_state'],
            'scan_attempts' => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'after' => 'scan_status'],
            'scan_error' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'scan_attempts'],
            'scanned_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'scan_error'],
        ]);
        $this->forge->addColumn('admission_notification_outbox', [
            'idempotency_key' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'payload_json'],
            'locked_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'attempts'],
            'locked_by' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'locked_at'],
            'last_error' => ['type' => 'TEXT', 'null' => true, 'after' => 'locked_by'],
            'failed_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'last_error'],
            'sent_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'failed_at'],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'operation' => ['type' => 'VARCHAR', 'constraint' => 120],
            'idempotency_key' => ['type' => 'VARCHAR', 'constraint' => 100],
            'request_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'resource_type' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'resource_id' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'response_json' => ['type' => 'LONGTEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'completed'],
            'created_at' => ['type' => 'DATETIME'],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tenant_id', 'operation', 'idempotency_key'], 'uq_idempotency_operation_key');
        $this->forge->addKey(['tenant_id', 'expires_at'], false, false, 'idx_idempotency_expiry');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'RESTRICT', 'CASCADE', 'fk_idempotency_tenant');
        $this->forge->createTable('idempotency_records', true);

        $this->forge->addField([
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'namespace' => ['type' => 'VARCHAR', 'constraint' => 120],
            'revision' => ['type' => 'BIGINT', 'unsigned' => true, 'default' => 1],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey(['tenant_id', 'namespace'], true, true, 'pk_tenant_cache_revision');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'RESTRICT', 'CASCADE', 'fk_cache_revision_tenant');
        $this->forge->createTable('tenant_cache_revisions', true);

        if ($this->db->DBDriver === 'SQLite3') {
            $applications = $this->db->prefixTable('applicant_applications');
            $snapshots = $this->db->prefixTable('application_submission_snapshots');
            $outbox = $this->db->prefixTable('admission_notification_outbox');
            $this->db->query('DROP INDEX IF EXISTS idx_applicant_programme_draft');
            $this->db->query("CREATE UNIQUE INDEX uq_applicant_programme_application ON {$applications} (tenant_id, applicant_profile_id, admission_cycle_id, programme_opening_id)");
            $this->db->query('DROP INDEX IF EXISTS uq_application_submission_snapshot');
            $this->db->query("CREATE UNIQUE INDEX uq_application_submission_snapshot_version ON {$snapshots} (tenant_id, applicant_application_id, submission_version)");
            $this->db->query("CREATE UNIQUE INDEX uq_admission_outbox_idempotency ON {$outbox} (tenant_id, channel, idempotency_key)");
            return;
        }

        $statements = [
            'ALTER TABLE applicant_applications DROP INDEX idx_applicant_programme_draft, ADD UNIQUE KEY uq_applicant_programme_application (tenant_id, applicant_profile_id, admission_cycle_id, programme_opening_id)',
            'ALTER TABLE application_submission_snapshots DROP INDEX uq_application_submission_snapshot, ADD UNIQUE KEY uq_application_submission_snapshot_version (tenant_id, applicant_application_id, submission_version)',
            'ALTER TABLE admission_notification_outbox ADD UNIQUE KEY uq_admission_outbox_idempotency (tenant_id, channel, idempotency_key), ADD KEY idx_admission_outbox_claim (status, available_at, locked_at)',
            'ALTER TABLE applicant_profiles ADD UNIQUE KEY uq_applicant_profiles_tenant_id (tenant_id, id)',
            'ALTER TABLE admission_cycles ADD UNIQUE KEY uq_admission_cycles_tenant_id (tenant_id, id)',
            'ALTER TABLE programmes ADD UNIQUE KEY uq_programmes_tenant_id (tenant_id, id)',
            'ALTER TABLE admission_programme_openings ADD UNIQUE KEY uq_admission_openings_tenant_id (tenant_id, id)',
            'ALTER TABLE applicant_applications ADD UNIQUE KEY uq_applicant_applications_tenant_id (tenant_id, id)',
            'ALTER TABLE application_documents ADD UNIQUE KEY uq_application_documents_tenant_id (tenant_id, id)',
            'ALTER TABLE admission_programme_openings ADD CONSTRAINT fk_opening_tenant_cycle FOREIGN KEY (tenant_id, admission_cycle_id) REFERENCES admission_cycles (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT',
            'ALTER TABLE admission_programme_openings ADD CONSTRAINT fk_opening_tenant_programme FOREIGN KEY (tenant_id, programme_id) REFERENCES programmes (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT',
            'ALTER TABLE applicant_applications ADD CONSTRAINT fk_application_tenant_profile FOREIGN KEY (tenant_id, applicant_profile_id) REFERENCES applicant_profiles (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT',
            'ALTER TABLE applicant_applications ADD CONSTRAINT fk_application_tenant_cycle FOREIGN KEY (tenant_id, admission_cycle_id) REFERENCES admission_cycles (tenant_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT',
            'ALTER TABLE applicant_applications ADD CONSTRAINT fk_application_tenant_opening FOREIGN KEY (tenant_id, programme_opening_id) REFERENCES admission_programme_openings (tenant_id, id) ON DELETE RESTRICT ON UPDATE RESTRICT',
            'ALTER TABLE application_documents ADD CONSTRAINT fk_document_tenant_profile FOREIGN KEY (tenant_id, applicant_profile_id) REFERENCES applicant_profiles (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT',
            'ALTER TABLE application_documents ADD CONSTRAINT fk_document_tenant_application FOREIGN KEY (tenant_id, application_id) REFERENCES applicant_applications (tenant_id, id) ON DELETE CASCADE ON UPDATE RESTRICT',
        ];
        foreach ($statements as $statement) {
            $this->db->query($statement);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('idempotency_records', true);
        $this->forge->dropTable('tenant_cache_revisions', true);
        if ($this->db->DBDriver === 'SQLite3') {
            $applications = $this->db->prefixTable('applicant_applications');
            $snapshots = $this->db->prefixTable('application_submission_snapshots');
            $this->db->query('DROP INDEX IF EXISTS uq_applicant_programme_application');
            $this->db->query("CREATE INDEX idx_applicant_programme_draft ON {$applications} (tenant_id, applicant_profile_id, admission_cycle_id, programme_opening_id)");
            $this->db->query('DROP INDEX IF EXISTS uq_admission_outbox_idempotency');
            $this->db->query('DROP INDEX IF EXISTS uq_application_submission_snapshot_version');
            $this->db->query("CREATE UNIQUE INDEX uq_application_submission_snapshot ON {$snapshots} (tenant_id, applicant_application_id)");
        } else {
            $this->db->query('ALTER TABLE application_documents DROP FOREIGN KEY fk_document_tenant_application, DROP FOREIGN KEY fk_document_tenant_profile');
            $this->db->query('ALTER TABLE applicant_applications DROP FOREIGN KEY fk_application_tenant_opening, DROP FOREIGN KEY fk_application_tenant_cycle, DROP FOREIGN KEY fk_application_tenant_profile');
            $this->db->query('ALTER TABLE applicant_applications DROP INDEX uq_applicant_programme_application, ADD INDEX idx_applicant_programme_draft (tenant_id, applicant_profile_id, admission_cycle_id, programme_opening_id)');
            $this->db->query('ALTER TABLE admission_programme_openings DROP FOREIGN KEY fk_opening_tenant_programme, DROP FOREIGN KEY fk_opening_tenant_cycle');
            $this->db->query('ALTER TABLE application_documents DROP INDEX uq_application_documents_tenant_id');
            $this->db->query('ALTER TABLE applicant_applications DROP INDEX uq_applicant_applications_tenant_id');
            $this->db->query('ALTER TABLE applicant_profiles DROP INDEX uq_applicant_profiles_tenant_id');
            $this->db->query('ALTER TABLE admission_programme_openings DROP INDEX uq_admission_openings_tenant_id');
            $this->db->query('ALTER TABLE programmes DROP INDEX uq_programmes_tenant_id');
            $this->db->query('ALTER TABLE admission_cycles DROP INDEX uq_admission_cycles_tenant_id');
            $this->db->query('ALTER TABLE admission_notification_outbox DROP INDEX uq_admission_outbox_idempotency, DROP INDEX idx_admission_outbox_claim');
            $this->db->query('ALTER TABLE application_submission_snapshots DROP INDEX uq_application_submission_snapshot_version, ADD UNIQUE KEY uq_application_submission_snapshot (tenant_id, applicant_application_id)');
        }
        $this->forge->dropColumn('admission_notification_outbox', ['sent_at', 'failed_at', 'last_error', 'locked_by', 'locked_at', 'idempotency_key']);
        $this->forge->dropColumn('application_documents', ['scanned_at', 'scan_error', 'scan_attempts', 'scan_status', 'storage_state']);
        $this->forge->dropColumn('application_submission_snapshots', 'submission_version');
        $this->forge->dropColumn('applicant_applications', ['submission_version', 'lock_version']);
    }

    private function assertNoTenantMismatch(): void
    {
        $table = fn (string $name): string => $this->db->prefixTable($name);
        $checks = [
            'duplicate applications' => 'SELECT MIN(id) AS id FROM ' . $table('applicant_applications') . ' GROUP BY tenant_id, applicant_profile_id, admission_cycle_id, programme_opening_id HAVING COUNT(*)>1 LIMIT 20',
            'documents/profile' => 'SELECT d.id FROM ' . $table('application_documents') . ' d JOIN ' . $table('applicant_profiles') . ' p ON p.id=d.applicant_profile_id WHERE d.tenant_id<>p.tenant_id LIMIT 20',
            'documents/application' => 'SELECT d.id FROM ' . $table('application_documents') . ' d JOIN ' . $table('applicant_applications') . ' a ON a.id=d.application_id WHERE d.application_id IS NOT NULL AND d.tenant_id<>a.tenant_id LIMIT 20',
            'applications/profile' => 'SELECT a.id FROM ' . $table('applicant_applications') . ' a JOIN ' . $table('applicant_profiles') . ' p ON p.id=a.applicant_profile_id WHERE a.tenant_id<>p.tenant_id LIMIT 20',
            'applications/cycle' => 'SELECT a.id FROM ' . $table('applicant_applications') . ' a JOIN ' . $table('admission_cycles') . ' c ON c.id=a.admission_cycle_id WHERE a.tenant_id<>c.tenant_id LIMIT 20',
            'applications/opening' => 'SELECT a.id FROM ' . $table('applicant_applications') . ' a JOIN ' . $table('admission_programme_openings') . ' o ON o.id=a.programme_opening_id WHERE a.tenant_id<>o.tenant_id LIMIT 20',
            'openings/cycle' => 'SELECT o.id FROM ' . $table('admission_programme_openings') . ' o JOIN ' . $table('admission_cycles') . ' c ON c.id=o.admission_cycle_id WHERE o.tenant_id<>c.tenant_id LIMIT 20',
            'openings/programme' => 'SELECT o.id FROM ' . $table('admission_programme_openings') . ' o JOIN ' . $table('programmes') . ' p ON p.id=o.programme_id WHERE o.tenant_id<>p.tenant_id LIMIT 20',
        ];
        foreach ($checks as $relationship => $sql) {
            $conflicts = $this->db->query($sql)->getResultArray();
            if ($conflicts !== []) {
                throw new RuntimeException('Phase 2 preflight failed for ' . $relationship . ': ' . json_encode($conflicts));
            }
        }
    }
}
