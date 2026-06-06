<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;

/** Documents Phase 3 database tables that must remain tenant-owned. @internal */
final class AdmissionPhase3SubmissionTest extends CIUnitTestCase
{
    public function testMigrationContainsTenantScopedSubmissionTables(): void
    {
        $migration = file_get_contents(ROOTPATH . 'app/Database/Migrations/2026-06-03-120000_CreateApplicantSubmissionTables.php');
        foreach (['application_olevel_sittings', 'application_olevel_results', 'application_submission_snapshots', 'admission_notification_outbox'] as $table) {
            $this->assertStringContainsString($table, $migration);
        }
        $this->assertStringContainsString("'tenant_id'", $migration);
        $this->assertStringContainsString('uq_applicant_application_number', $migration);
    }
}
