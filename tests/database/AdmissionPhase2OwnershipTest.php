<?php

use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use App\Services\Admissions\AdmissionConfigurationService;
use App\Services\Admissions\ApplicantProfileService;
use App\Services\Admissions\ApplicationDraftService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Admissions\AdmissionContextHelper;

/** Verifies Phase 2 tenant applicant profiles, draft ownership, and biodata autosave. @internal */
final class AdmissionPhase2OwnershipTest extends CIUnitTestCase
{
    use AdmissionContextHelper;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testApplicantCanCreateProfileStartDraftAndSaveBiodata(): void
    {
        $tenant = $this->createAdmissionTenant('phase2-a');
        $opening = $this->prepareOpenProgramme($tenant, 'phase2-a', 2101, 'A');
        $this->authenticateAdmissionUser(2201);
        $profile = (new ApplicantProfileService())->ensureProfile('0803 123 4567');
        $this->assertSame('+2348031234567', $profile['phone_e164']);

        $draft = (new ApplicationDraftService())->start($opening);
        $this->assertSame('draft', $draft['status']);
        $this->assertNotEmpty($draft['public_token']);

        $saved = (new ApplicationDraftService())->saveBiodata($draft['public_token'], ['surname' => 'Okoro', 'first_name' => 'Ada', 'phone_e164' => '08031234567', 'email' => 'ADA@EXAMPLE.COM', 'nationality' => 'Nigerian']);
        $this->assertSame('+2348031234567', $saved['biodata']['phone_e164']);
        $this->assertSame('ada@example.com', $saved['biodata']['email']);
        $this->assertGreaterThan(0, (int) $saved['biodata']['completion_percent']);
    }

    public function testApplicantCannotResumeAnotherApplicantsDraft(): void
    {
        $tenant = $this->createAdmissionTenant('phase2-owner');
        $opening = $this->prepareOpenProgramme($tenant, 'phase2-owner', 2301, 'Owner');
        $this->authenticateAdmissionUser(2302);
        (new ApplicantProfileService())->ensureProfile('owner@example.com');
        $draft = (new ApplicationDraftService())->start($opening);

        $this->authenticateAdmissionUser(2303);
        (new ApplicantProfileService())->ensureProfile('other@example.com');
        $this->assertNull(service('applicantAccessPolicy')->ownedApplication(service('tenantContextManager')->current(), $draft['public_token']));
    }

    public function testDraftApplicationsCannotCrossTenantContext(): void
    {
        $tenantA = $this->createAdmissionTenant('phase2-cross-a');
        $tenantB = $this->createAdmissionTenant('phase2-cross-b');
        $openingA = $this->prepareOpenProgramme($tenantA, 'phase2-cross-a', 2401, 'Cross A');
        $this->authenticateAdmissionUser(2402);
        (new ApplicantProfileService())->ensureProfile('cross@example.com');
        $draft = (new ApplicationDraftService())->start($openingA);

        $this->useAdmissionTenant($tenantB, 'phase2-cross-b');
        $this->authenticateAdmissionUser(2402);
        (new ApplicantProfileService())->ensureProfile('cross@example.com');
        $this->assertNull((new ApplicantApplicationModel())->find($draft['id']));
        $this->assertNull(service('applicantAccessPolicy')->ownedApplication(service('tenantContextManager')->current(), $draft['public_token']));
    }

    private function prepareOpenProgramme(int $tenantId, string $slug, int $managerId, string $suffix): int
    {
        $this->useAdmissionTenant($tenantId, $slug);
        $this->createStaffContext($tenantId, $managerId);
        foreach (['admissions.cycles.manage', 'admissions.programmes.manage'] as $authority) {
            $this->grantAdmissionAuthority($managerId, $authority);
        }
        $session = $this->createAdmissionAcademicSession('2026/' . $suffix);
        $academic = $this->createAdmissionAcademicStructure($suffix);
        $cycle = (new AdmissionConfigurationService())->saveCycle(['academic_session_id' => $session, 'title' => 'Admission ' . $suffix, 'code' => 'P2-' . strtoupper(str_replace(' ', '-', $suffix)), 'opens_at' => date('Y-m-d H:i:s', time() - 3600), 'closes_at' => date('Y-m-d H:i:s', time() + 86400), 'status' => 'open', 'is_public' => 1]);

        return (new AdmissionConfigurationService())->saveProgrammeOpening(array_merge($academic, ['admission_cycle_id' => $cycle, 'status' => 'open', 'screening_method' => 'manual_review']));
    }
}
