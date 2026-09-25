<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class Phase3AdministrationUiTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    protected $migrate = true;
    protected $namespace = 'App';

    public function testPlatformAdministratorCanUseTenantManagementPage(): void
    {
        $result = $this->withSession(['user_id' => 7001, 'auth_groups' => ['platform_admin']])->get('platform/tenants');
        $result->assertStatus(200);
        $result->assertSee('Create school');
    }

    public function testTenantAdministratorCanUseConfigurationPage(): void
    {
        $tenantId = $this->tenantAdmin(7002);
        $result = $this->withSession(['user_id' => 7002, 'auth_groups' => ['tenant_super_admin']])
            ->withHeaders(['Host' => 'admin-school.test'])->get('tenant/admin/configuration');
        $result->assertStatus(200);
        $result->assertSee('School configuration');
        $result->assertSee('Programme-course mapping');
        $this->assertGreaterThan(0, $tenantId);
    }

    public function testTenantAdminPageRejectsCrossTenantHost(): void
    {
        $this->tenantAdmin(7003);
        $other = $this->db->table('tenants')->insert(['school_name' => 'Other', 'slug' => 'other-school', 'status' => 'active']);
        $otherId = (int) $this->db->insertID();
        $this->db->table('tenant_domains')->insert(['tenant_id' => $otherId, 'domain' => 'other-school.test', 'status' => 'active']);
        $result = $this->withSession(['user_id' => 7003, 'auth_groups' => ['tenant_super_admin']])
            ->withHeaders(['Host' => 'other-school.test', 'Accept' => 'application/json'])->get('tenant/admin');
        $result->assertStatus(403);
    }

    private function tenantAdmin(int $userId): int
    {
        $this->db->table('tenants')->insert(['school_name' => 'Admin School', 'slug' => 'admin-school', 'status' => 'active']);
        $tenantId = (int) $this->db->insertID();
        $this->db->table('tenant_domains')->insert(['tenant_id' => $tenantId, 'domain' => 'admin-school.test', 'status' => 'active']);
        $this->db->table('tenant_memberships')->insert(['tenant_id' => $tenantId, 'user_id' => $userId, 'status' => 'active', 'is_active' => 1]);
        $this->db->table('operational_authorities')->insert(['tenant_id' => $tenantId, 'code' => 'school.configuration.manage', 'name' => 'School configuration', 'is_active' => 1]);
        $authority = (int) $this->db->insertID();
        $this->db->table('membership_authorities')->insert(['tenant_id' => $tenantId, 'user_id' => $userId, 'authority_id' => $authority]);
        return $tenantId;
    }
}
