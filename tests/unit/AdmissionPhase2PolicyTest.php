<?php

use CodeIgniter\Test\CIUnitTestCase;

/** Guards the approved Phase 2 applicant-access and draft boundary. @internal */
final class AdmissionPhase2PolicyTest extends CIUnitTestCase
{
    public function testMigrationDefinesApplicantDraftTablesOnly(): void
    {
        $migration = file_get_contents(APPPATH . 'Database/Migrations/2026-06-03-100000_CreateApplicantDraftApplicationTables.php');
        $this->assertStringContainsString("createTable('applicant_applications'", $migration);
        $this->assertStringContainsString("createTable('application_biodata_drafts'", $migration);
        $this->assertStringNotContainsString("createTable('application_olevel", $migration);
        $this->assertStringNotContainsString("submitted_at", $migration);
    }

    public function testApplicantRoutesRequireTenantContextAndApplicantOwnership(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $this->assertStringContainsString("'Applicant\\ProfileController::save'", $routes);
        $this->assertStringContainsString("'filter' => 'csrf,protectedAuth,tenantContext:required,applicantAccess'", $routes);
        $this->assertStringContainsString("Applicant\\ApplicationController::saveBiodata", $routes);
    }

    public function testDraftServiceDoesNotImplementSubmissionOrUploads(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Admissions/ApplicationDraftService.php');
        $this->assertStringContainsString('public function start', $service);
        $this->assertStringContainsString('public function saveBiodata', $service);
        $this->assertStringNotContainsString('submitApplication', $service);
        $this->assertStringNotContainsString('upload', $service);
    }

    public function testVueAutosaveCompositionApiIsPresent(): void
    {
        $asset = file_get_contents(ROOTPATH . 'resources/js/applicant-draft.js');
        $this->assertStringContainsString("from 'vue'", $asset);
        $this->assertStringContainsString('useApplicantDraft', $asset);
        $this->assertStringContainsString('Retry shortly', $asset);
    }
}
