<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;

/** Documents Phase 5 decision/offer tenant-scope tables. @internal */
final class AdmissionPhase5DecisionOfferTest extends CIUnitTestCase
{
    public function testMigrationCreatesTenantScopedDecisionTables(): void
    {
        $migration = file_get_contents(ROOTPATH . 'app/Database/Migrations/2026-06-03-160000_CreateAdmissionDecisionOfferTables.php');
        foreach (['admission_shortlist_batches', 'admission_shortlist_entries', 'admission_decisions', 'admission_offers'] as $table) {
            $this->assertStringContainsString($table, $migration);
        }
        $this->assertStringContainsString("'tenant_id'", $migration);
        $this->assertStringContainsString('uq_admission_offer_reference', $migration);
        $this->assertStringContainsString('offer_snapshot_json', $migration);
    }
}
