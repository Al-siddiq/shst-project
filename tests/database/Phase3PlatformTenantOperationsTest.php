<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

final class Phase3PlatformTenantOperationsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    protected $migrate = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        service('session')->set(['user_id' => 7100, 'auth_groups' => ['platform_admin']]);
    }

    public function testTenantLifecycleAndDomainAreAudited(): void
    {
        $id = service('platformTenant')->create(['school_name' => 'Lifecycle School', 'slug' => 'lifecycle-school']);
        service('platformTenant')->saveDomain($id, ['domain' => 'lifecycle.example.test', 'type' => 'custom_domain', 'is_primary' => 1]);
        service('platformTenant')->transition($id, 'active', 'Initial configuration has been verified.');

        $this->assertSame('active', $this->db->table('tenants')->where('id', $id)->get()->getRowArray()['status']);
        $this->assertSame(1, $this->db->table('tenant_domains')->where('tenant_id', $id)->where('is_primary', 1)->countAllResults());
        $this->assertSame(3, $this->db->table('audit_logs')->where('tenant_id', $id)->countAllResults());
    }
}
