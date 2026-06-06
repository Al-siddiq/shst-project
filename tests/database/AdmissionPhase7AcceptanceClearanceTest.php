<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;

/** Documents Phase 7 acceptance, clearance, and handoff tables. @internal */
final class AdmissionPhase7AcceptanceClearanceTest extends CIUnitTestCase
{
    public function testMigrationCreatesAcceptanceClearanceAndEligibilityTables(): void
    {
        $migration = file_get_contents(ROOTPATH . 'app/Database/Migrations/2026-06-04-120000_CreateAdmissionAcceptanceClearanceTables.php');
        foreach (['admission_offer_acceptances', 'admission_clearance_statuses', 'admission_conversion_eligibility_markers'] as $table) {
            $this->assertStringContainsString($table, $migration);
        }
        $this->assertStringContainsString("'tenant_id'", $migration);
        $this->assertStringContainsString('acceptance_fee_status', $migration);
        $this->assertStringContainsString('handoff_payload_json', $migration);
    }
}
