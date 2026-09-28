<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** Proves that the canonical chain reaches its expected terminal schema. */
final class MigrationChainBaselineTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testFreshChainCreatesTerminalSchemaWithoutDuplicateResponsibilities(): void
    {
        foreach ([
            'tenants', 'tenant_memberships', 'tenant_membership_history',
            'applicant_profiles', 'applicant_applications', 'application_documents',
            'application_submission_snapshots', 'admission_notification_outbox',
            'idempotency_records', 'tenant_cache_revisions', 'in_app_notifications',
        ] as $table) {
            $this->assertTrue($this->db->tableExists($table), 'Missing terminal table: ' . $table);
        }

        $membershipFields = $this->db->getFieldNames('tenant_memberships');
        $this->assertContains('status', $membershipFields);
        $this->assertContains('membership_label', $membershipFields);
        $this->assertContains('scan_locked_at', $this->db->getFieldNames('application_documents'));
        $this->assertContains('provider_message_id', $this->db->getFieldNames('admission_notification_outbox'));
    }

    public function testMigrationHistoryHasNoDuplicateVersionClassRows(): void
    {
        $duplicates = $this->db->table('migrations')
            ->select('version, class, namespace, COUNT(*) AS occurrences')
            ->groupBy(['version', 'class', 'namespace'])
            ->having('COUNT(*) >', 1)
            ->get()->getResultArray();

        $this->assertSame([], $duplicates);
    }
}
