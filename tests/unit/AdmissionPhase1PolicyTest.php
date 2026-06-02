<?php

use CodeIgniter\Test\CIUnitTestCase;

/** Guards the approved Phase 1 admission-configuration contract. @internal */
final class AdmissionPhase1PolicyTest extends CIUnitTestCase
{
    public function testMigrationDefinesTenantOwnedAdmissionConfigurationTables(): void
    {
        $migration = file_get_contents(APPPATH . 'Database/Migrations/2026-06-02-120000_CreateAdmissionConfigurationTables.php');
        foreach (['admission_cycles', 'admission_programme_openings', 'admission_requirement_definitions', 'admission_subject_requirements', 'admission_document_requirements'] as $table) {
            $this->assertStringContainsString("createTable('{$table}'", $migration);
        }
        $this->assertStringContainsString("'tenant_id'", $migration);
    }

    public function testConfigurationServiceRepeatsAuthorityAndTenantRelationshipChecks(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Admissions/AdmissionConfigurationService.php');
        $this->assertStringContainsString("assertAuthority('admissions.cycles.manage')", $service);
        $this->assertStringContainsString("assertAuthority('admissions.programmes.manage')", $service);
        $this->assertStringContainsString("assertAuthority('admissions.requirements.manage')", $service);
        $this->assertStringContainsString('Programme and department must belong to the same tenant relationship.', $service);
        $this->assertStringContainsString('Requirement programme opening must belong to the selected tenant admission cycle.', $service);
    }

    public function testPublicDiscoveryFiltersCycleAndProgrammeVisibility(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Admissions/PublicAdmissionService.php');
        $this->assertStringContainsString("where('status', 'open')", $service);
        $this->assertStringContainsString("where('is_public', 1)", $service);
        $this->assertStringContainsString("(int) (\$programme['is_active'] ?? 0) !== 1", $service);
        $this->assertStringContainsString('array_intersect_key', $service);
    }

    public function testRoutesExposePublicDiscoveryAndNarrowTenantAuthorities(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $this->assertStringContainsString("admissions/programmes", $routes);
        $this->assertStringContainsString('tenantAccess:authority:admissions.cycles.manage', $routes);
        $this->assertStringContainsString('tenantAccess:authority:admissions.programmes.manage', $routes);
        $this->assertStringContainsString('tenantAccess:authority:admissions.requirements.manage', $routes);
    }
}
