<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class Phase1SecurityBoundaryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAnonymousCallerCannotListPlatformTenants(): void
    {
        $result = $this->withHeaders(['Accept' => 'application/json'])->get('platform/tenants');

        $result->assertStatus(401);
    }

    public function testTenantIdentityCannotEnterPlatformArea(): void
    {
        $tenant = $this->tenant('platform-denied');
        $this->db->table('tenant_memberships')->insert([
            'tenant_id' => $tenant,
            'user_id' => 901,
            'status' => 'active',
            'is_active' => 1,
        ]);

        $result = $this->withSession(['user_id' => 901, 'auth_groups' => ['tenant_super_admin']])
            ->withHeaders(['Accept' => 'application/json'])
            ->get('platform/tenants');

        $result->assertStatus(403);
    }

    public function testPlatformIdentityWithoutTenantMembershipCanListTenants(): void
    {
        $this->tenant('platform-visible');

        $result = $this->withSession(['user_id' => 902, 'auth_groups' => ['platform_admin']])
            ->withHeaders(['Accept' => 'application/json'])
            ->get('platform/tenants');

        $result->assertStatus(200);
    }

    public function testTenantHostCannotOverrideAuthenticatedMembership(): void
    {
        $tenantA = $this->tenant('member-a');
        $tenantB = $this->tenant('member-b');
        $this->db->table('tenant_domains')->insert([
            'tenant_id' => $tenantB,
            'domain' => 'member-b.test',
            'status' => 'active',
        ]);
        $this->db->table('tenant_memberships')->insert([
            'tenant_id' => $tenantA,
            'user_id' => 903,
            'status' => 'active',
            'is_active' => 1,
        ]);

        $result = $this->withSession(['user_id' => 903, 'auth_groups' => ['tenant_admin']])
            ->withHeaders(['Accept' => 'application/json', 'Host' => 'member-b.test'])
            ->get('tenant/navigation');

        $result->assertStatus(403);
    }

    public function testBlockOneMutationWithoutCsrfIsRejected(): void
    {
        $tenant = $this->tenant('csrf-tenant');
        $this->db->table('tenant_domains')->insert([
            'tenant_id' => $tenant,
            'domain' => 'csrf-tenant.test',
            'status' => 'active',
        ]);
        $this->db->table('tenant_memberships')->insert([
            'tenant_id' => $tenant,
            'user_id' => 904,
            'status' => 'active',
            'is_active' => 1,
        ]);

        $result = $this->withSession(['user_id' => 904, 'auth_groups' => ['tenant_admin']])
            ->withHeaders(['Accept' => 'application/json', 'Host' => 'csrf-tenant.test', 'Content-Type' => 'application/json'])
            ->withBody(json_encode(['institution_name' => 'CSRF School']))
            ->post('tenant/config/profile');

        $result->assertStatus(403);
    }

    private function tenant(string $slug): int
    {
        $this->db->table('tenants')->insert(['school_name' => $slug, 'slug' => $slug, 'status' => 'active']);

        return (int) $this->db->insertID();
    }
}
