<?php

namespace Tests\Support\Admissions;

use App\Entities\TenantContext;
use App\Models\Tenant\Admissions\ApplicantProfileModel;
use App\Models\TenantMembershipModel;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use App\Models\Tenant\AcademicSessionModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\LevelModel;
use App\Models\Tenant\ProgrammeModel;
use CodeIgniter\Test\DatabaseTestTrait;

/** Reusable tenant, applicant, and staff contexts for Block 3 database tests. */
trait AdmissionContextHelper
{
    use DatabaseTestTrait;

    protected function createAdmissionTenant(string $slug): int
    {
        $this->db->table('tenants')->insert(['school_name' => $slug, 'slug' => $slug, 'status' => 'active']);

        return (int) $this->db->insertID();
    }

    protected function useAdmissionTenant(int $tenantId, string $slug): void
    {
        service('tenantContextManager')->set(new TenantContext($tenantId, $slug, 'route_slug'));
    }

    protected function authenticateAdmissionUser(int $userId): void
    {
        // IdentityGuard retains this session fallback for isolated repository
        // tests while production authentication is delegated to Shield.
        session()->set('user_id', $userId);
    }

    protected function createApplicantContext(int $userId, array $overrides = []): int
    {
        $this->authenticateAdmissionUser($userId);

        return (int) (new ApplicantProfileModel())->insert(array_merge(['user_id' => $userId], $overrides), true);
    }

    protected function createStaffContext(int $tenantId, int $userId): int
    {
        $this->authenticateAdmissionUser($userId);

        return (int) (new TenantMembershipModel())->insert([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'status' => 'active',
            'is_active' => 1,
        ], true);
    }
    protected function grantAdmissionAuthority(int $userId, string $code): void
    {
        $authorityId = (int) (new OperationalAuthorityModel())->insert(['code' => $code, 'name' => $code, 'is_active' => 1], true);
        (new MembershipAuthorityModel())->insert(['user_id' => $userId, 'authority_id' => $authorityId]);
    }

    protected function createAdmissionAcademicSession(string $name = '2026/2027'): int
    {
        return (int) (new AcademicSessionModel())->insert(['name' => $name, 'status' => 'active'], true);
    }

    /** @return array{department_id: int, programme_id: int, level_id: int} */
    protected function createAdmissionAcademicStructure(string $suffix): array
    {
        $departmentId = (int) (new DepartmentModel())->insert(['name' => 'Department ' . $suffix, 'is_active' => 1], true);
        $programmeId = (int) (new ProgrammeModel())->insert(['department_id' => $departmentId, 'name' => 'Programme ' . $suffix, 'is_active' => 1], true);
        $levelId = (int) (new LevelModel())->insert(['name' => 'Level ' . $suffix, 'is_active' => 1], true);

        return ['department_id' => $departmentId, 'programme_id' => $programmeId, 'level_id' => $levelId];
    }

}
