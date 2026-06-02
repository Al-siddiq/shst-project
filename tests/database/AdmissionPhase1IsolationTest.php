<?php

use App\Models\Tenant\Admissions\AdmissionCycleModel;
use App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel;
use App\Services\Admissions\AdmissionConfigurationService;
use App\Services\Admissions\PublicAdmissionService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Admissions\AdmissionContextHelper;

/** Exercises tenant-safe Phase 1 setup and sanitized public discovery. @internal */
final class AdmissionPhase1IsolationTest extends CIUnitTestCase
{
    use AdmissionContextHelper;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testPublicDiscoveryReturnsOnlyActiveTenantOpenProgrammes(): void
    {
        $tenantA = $this->createAdmissionTenant('phase1-a');
        $tenantB = $this->createAdmissionTenant('phase1-b');
        $this->prepareManager($tenantA, 'phase1-a', 1101);
        $sessionA = $this->createAdmissionAcademicSession();
        $academicA = $this->createAdmissionAcademicStructure('A');
        $cycleA = $this->createOpenCycle($sessionA, 'A');
        $openingA = $this->createOpening($cycleA, $academicA, 'open');
        $hiddenAcademicA = $this->createAdmissionAcademicStructure('Hidden A');
        $this->createOpening($cycleA, $hiddenAcademicA, 'hidden', 'Hidden A');
        $this->createRequirements($cycleA, $openingA);

        $publicA = new PublicAdmissionService();
        $this->assertSame('Admission A', $publicA->activeCycle()['title']);
        $programmes = $publicA->openProgrammes();
        $this->assertSame(['Programme A'], array_column($programmes, 'programme_name'));
        $this->assertArrayNotHasKey('tenant_id', $programmes[0]);
        $this->assertArrayNotHasKey('created_by', $programmes[0]);
        $this->assertCount(1, $publicA->programme($openingA)['subject_requirements']);
        $this->assertCount(1, $publicA->programme($openingA)['document_requirements']);

        $this->prepareManager($tenantB, 'phase1-b', 1102);
        $this->assertNull((new PublicAdmissionService())->activeCycle());
        $this->assertNull((new AdmissionCycleModel())->find($cycleA));
        $this->assertNull((new AdmissionProgrammeOpeningModel())->find($openingA));
    }

    public function testProgrammeOpeningRejectsCrossTenantAcademicReferences(): void
    {
        $tenantA = $this->createAdmissionTenant('phase1-cross-a');
        $tenantB = $this->createAdmissionTenant('phase1-cross-b');
        $this->prepareManager($tenantA, 'phase1-cross-a', 1201);
        $sessionA = $this->createAdmissionAcademicSession();
        $cycleA = $this->createOpenCycle($sessionA, 'Cross');
        $this->useAdmissionTenant($tenantB, 'phase1-cross-b');
        $academicB = $this->createAdmissionAcademicStructure('Cross B');
        $this->useAdmissionTenant($tenantA, 'phase1-cross-a');
        $this->authenticateAdmissionUser(1201);

        $this->expectException(InvalidArgumentException::class);
        (new AdmissionConfigurationService())->saveProgrammeOpening(array_merge($academicB, ['admission_cycle_id' => $cycleA, 'status' => 'open', 'screening_method' => 'manual_review']));
    }

    public function testCycleLifecycleRejectsInvalidArchivedToOpenTransition(): void
    {
        $tenant = $this->createAdmissionTenant('phase1-lifecycle');
        $this->prepareManager($tenant, 'phase1-lifecycle', 1301);
        $session = $this->createAdmissionAcademicSession();
        $service = new AdmissionConfigurationService();
        $cycle = $service->saveCycle($this->cycleData($session, 'Lifecycle', 'draft'));
        $service->transitionCycle($cycle, 'open');
        $service->transitionCycle($cycle, 'closed');
        $service->transitionCycle($cycle, 'under_review');
        $service->transitionCycle($cycle, 'archived');
        $actions = array_column($this->db->table('audit_logs')->where('tenant_id', $tenant)->get()->getResultArray(), 'action');
        $this->assertContains('admissions.cycle.opened', $actions);
        $this->assertContains('admissions.cycle.closed', $actions);
        $this->assertContains('admissions.cycle.archived', $actions);

        $this->expectException(InvalidArgumentException::class);
        $service->transitionCycle($cycle, 'open');
    }

    private function prepareManager(int $tenantId, string $slug, int $userId): void
    {
        $this->useAdmissionTenant($tenantId, $slug);
        $this->createStaffContext($tenantId, $userId);
        foreach (['admissions.cycles.manage', 'admissions.programmes.manage', 'admissions.requirements.manage'] as $authority) {
            $this->grantAdmissionAuthority($userId, $authority);
        }
    }

    private function createOpenCycle(int $sessionId, string $suffix): int
    {
        return (new AdmissionConfigurationService())->saveCycle($this->cycleData($sessionId, $suffix, 'open'));
    }

    /** @return array<string, mixed> */
    private function cycleData(int $sessionId, string $suffix, string $status): array
    {
        return ['academic_session_id' => $sessionId, 'title' => 'Admission ' . $suffix, 'code' => 'ADM-' . strtoupper($suffix), 'opens_at' => date('Y-m-d H:i:s', time() - 3600), 'closes_at' => date('Y-m-d H:i:s', time() + 86400), 'status' => $status, 'is_public' => 1];
    }

    /** @param array{department_id: int, programme_id: int, level_id: int} $academic */
    private function createOpening(int $cycleId, array $academic, string $status, string $summary = 'Visible A'): int
    {
        return (new AdmissionConfigurationService())->saveProgrammeOpening(array_merge($academic, ['admission_cycle_id' => $cycleId, 'status' => $status, 'screening_method' => 'manual_review', 'requirement_summary' => $summary]));
    }

    private function createRequirements(int $cycleId, int $openingId): void
    {
        $service = new AdmissionConfigurationService();
        $service->saveRequirement(['admission_cycle_id' => $cycleId, 'programme_opening_id' => $openingId, 'requirement_type' => 'biodata', 'code' => 'surname', 'label' => 'Surname', 'status' => 'active']);
        $service->saveSubjectRequirement(['admission_cycle_id' => $cycleId, 'programme_opening_id' => $openingId, 'subject_code' => 'ENG', 'subject_name' => 'English Language', 'minimum_grade' => 'C6', 'status' => 'active']);
        $service->saveDocumentRequirement(['admission_cycle_id' => $cycleId, 'programme_opening_id' => $openingId, 'document_type' => 'passport', 'label' => 'Passport photograph', 'allowed_mime_types' => 'image/jpeg,image/png', 'maximum_size_bytes' => 1048576, 'status' => 'active']);
    }
}
