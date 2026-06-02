<?php

namespace Tests\Support\Admissions;

use App\Entities\TenantContext;
use App\Models\Tenant\Admissions\ApplicantProfileModel;
use App\Models\TenantMembershipModel;
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
}
