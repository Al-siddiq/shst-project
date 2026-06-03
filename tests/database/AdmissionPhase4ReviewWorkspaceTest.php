<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;

/** Documents Phase 4 review tables and tenant ownership requirements. @internal */
final class AdmissionPhase4ReviewWorkspaceTest extends CIUnitTestCase
{
    public function testMigrationCreatesTenantScopedReviewTables(): void
    {
        $migration = file_get_contents(ROOTPATH . 'app/Database/Migrations/2026-06-03-140000_CreateAdmissionReviewWorkspaceTables.php');
        foreach (['admission_application_reviews', 'admission_document_review_logs', 'admission_screening_records'] as $table) {
            $this->assertStringContainsString($table, $migration);
        }
        $this->assertStringContainsString("'tenant_id'", $migration);
        $this->assertStringContainsString('private_notes', $migration);
        $this->assertStringContainsString('correction_allowed_until', $migration);
    }
}
