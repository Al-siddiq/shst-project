<?php

use App\Entities\TenantContext;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\Website\DepartmentPublicProfileModel;
use App\Services\Website\PublicShowcaseService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Exercises actual tenant isolation and active-record filtering for Phase 2.
 *
 * @internal
 */
final class WebsitePhase2IsolationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testDepartmentListingExcludesOtherTenantAndInactiveRecords(): void
    {
        $tenantA = $this->insertTenant('tenant-a');
        $tenantB = $this->insertTenant('tenant-b');

        $this->setTenant($tenantA, 'tenant-a');
        $visibleDepartment = $this->insertDepartment('Visible Department', true);
        $inactiveDepartment = $this->insertDepartment('Inactive Department', false);
        $this->insertDepartmentProfile($visibleDepartment, 'visible-department');
        $this->insertDepartmentProfile($inactiveDepartment, 'inactive-department');

        $this->setTenant($tenantB, 'tenant-b');
        $otherDepartment = $this->insertDepartment('Other Tenant Department', true);
        $this->insertDepartmentProfile($otherDepartment, 'other-tenant-department');

        $this->setTenant($tenantA, 'tenant-a');
        $departments = (new PublicShowcaseService())->departments();

        $this->assertSame(['visible-department'], array_column($departments, 'slug'));
    }

    private function insertTenant(string $slug): int
    {
        $this->db->table('tenants')->insert(['school_name' => $slug, 'slug' => $slug, 'status' => 'active']);

        return (int) $this->db->insertID();
    }

    private function setTenant(int $id, string $slug): void
    {
        service('tenantContextManager')->set(new TenantContext($id, $slug, 'route_slug'));
    }

    private function insertDepartment(string $name, bool $active): int
    {
        return (int) (new DepartmentModel())->insert(['name' => $name, 'is_active' => $active ? 1 : 0], true);
    }

    private function insertDepartmentProfile(int $departmentId, string $slug): void
    {
        (new DepartmentPublicProfileModel())->insert(['department_id' => $departmentId, 'slug' => $slug, 'status' => 'published']);
    }
}
