<?php

namespace Tests\Database;

use App\Entities\TenantContext;
use App\Models\TenantIamGroupAssignmentModel;
use App\Models\TenantMembershipModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use InvalidArgumentException;

final class Phase1IdentityBoundaryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testShieldUserCannotBeBoundToASecondTenant(): void
    {
        $tenantA = $this->tenant('identity-a');
        $tenantB = $this->tenant('identity-b');
        service('session')->set(['user_id' => 801, 'auth_groups' => ['applicant']]);

        service('tenantIdentity')->bindUser(801, new TenantContext($tenantA, 'identity-a', 'route_slug'), 'Applicant');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('permanently bound to another tenant');
        service('tenantIdentity')->bindUser(801, new TenantContext($tenantB, 'identity-b', 'route_slug'), 'Applicant');
    }

    public function testLegacyIamAssignmentIsNotUsedForRuntimeBroadGroup(): void
    {
        $tenant = $this->tenant('shield-group-source');
        $context = new TenantContext($tenant, 'shield-group-source', 'route_slug');
        service('tenantContextManager')->set($context);
        service('session')->set(['user_id' => 802, 'auth_groups' => ['applicant']]);
        (new TenantMembershipModel())->insert(['tenant_id' => $tenant, 'user_id' => 802, 'status' => 'active', 'is_active' => 1]);
        (new TenantIamGroupAssignmentModel())->insert(['user_id' => 802, 'group_name' => 'tenant_super_admin']);

        $this->assertSame(['applicant'], service('tenantAccess')->groups($context));
        $this->assertFalse(service('tenantAccess')->hasGroup($context, 'tenant_super_admin'));
    }

    public function testPlatformAdministratorCannotReceiveTenantMembership(): void
    {
        $tenant = $this->tenant('platform-separated');
        service('session')->set(['user_id' => 803, 'auth_groups' => ['platform_admin']]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot receive tenant membership');
        service('tenantIdentity')->bindUser(803, new TenantContext($tenant, 'platform-separated', 'route_slug'));
    }

    public function testDatabaseEnforcesOneMembershipPerShieldUser(): void
    {
        $tenantA = $this->tenant('unique-a');
        $tenantB = $this->tenant('unique-b');
        $model = new TenantMembershipModel();
        $model->insert(['tenant_id' => $tenantA, 'user_id' => 804, 'status' => 'active', 'is_active' => 1]);

        $this->expectException(\Throwable::class);
        $model->insert(['tenant_id' => $tenantB, 'user_id' => 804, 'status' => 'active', 'is_active' => 1]);
    }

    private function tenant(string $slug): int
    {
        $this->db->table('tenants')->insert(['school_name' => $slug, 'slug' => $slug, 'status' => 'active']);

        return (int) $this->db->insertID();
    }
}
