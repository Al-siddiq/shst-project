<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;

/** Documents Phase 6 publication tables and safe-entry schema. @internal */
final class AdmissionPhase6PublicationTest extends CIUnitTestCase
{
    public function testMigrationCreatesTenantScopedPublicationTables(): void
    {
        $migration = file_get_contents(ROOTPATH . 'app/Database/Migrations/2026-06-04-100000_CreateAdmissionListPublicationTables.php');
        $this->assertStringContainsString('admission_list_publications', $migration);
        $this->assertStringContainsString('admission_list_entries', $migration);
        $this->assertStringContainsString("'tenant_id'", $migration);
        $this->assertStringContainsString('uq_admission_list_cycle_version', $migration);
        $this->assertStringContainsString('applicant_display_name', $migration);
        $this->assertStringNotContainsString('phone_e164', $migration);
    }
}
