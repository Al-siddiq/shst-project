<?php

use CodeIgniter\Test\CIUnitTestCase;

/** Protects the approved Phase 2 schema and admissions processing boundary. @internal */
final class WebsitePhase2PolicyTest extends CIUnitTestCase
{
    public function testMigrationDefinesPublicAcademicExtensionTables(): void
    {
        $migration = file_get_contents(APPPATH . 'Database/Migrations/2026-05-31-140000_CreatePublicAcademicShowcaseTables.php');

        $this->assertStringContainsString("createTable('department_public_profiles'", $migration);
        $this->assertStringContainsString("createTable('programme_public_profiles'", $migration);
        $this->assertStringContainsString("createTable('admission_information_pages'", $migration);
        $this->assertStringContainsString("'status' => ['type' => 'VARCHAR'", $migration);
    }

    public function testAdmissionControllerIsReadOnly(): void
    {
        $controller = file_get_contents(APPPATH . 'Controllers/PublicSite/AdmissionController.php');

        $this->assertStringContainsString('function index()', $controller);
        $this->assertStringNotContainsString('function create', $controller);
        $this->assertStringNotContainsString('function submit', $controller);
    }

    public function testPublicShowcaseRequiresPublishedExtensionsAndActiveAcademicRecords(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Website/PublicShowcaseService.php');

        $this->assertStringContainsString("where('status', 'published')", $service);
        $this->assertStringContainsString("(int) \$department['is_active'] !== 1", $service);
        $this->assertStringContainsString("(int) \$programme['is_active'] !== 1", $service);
    }
}
