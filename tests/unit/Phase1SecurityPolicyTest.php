<?php

namespace Tests\Unit;

use App\Libraries\Auth\IdentityGuard;
use CodeIgniter\Test\CIUnitTestCase;

final class Phase1SecurityPolicyTest extends CIUnitTestCase
{
    public function testIdentifierPolicyClassifiesEmailAndNigerianPhone(): void
    {
        $guard = new IdentityGuard();

        $this->assertSame('email', $guard->identifierType(' Person@Example.COM '));
        $this->assertSame('phone', $guard->identifierType('08031234567'));
        $this->assertNull($guard->identifierType('not-an-identifier'));
    }

    public function testRoutesProtectPlatformAndBlockOneMutations(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');

        $this->assertStringContainsString("group('platform', ['filter' => 'protectedAuth,platformAccess']", $routes);
        $this->assertStringContainsString("group('tenant/config', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:group:tenant_super_admin|tenant_admin:authority:school.configuration.manage']", $routes);
        $this->assertStringContainsString("group('tenant/access', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:group:tenant_super_admin:authority:tenant.access.manage']", $routes);
        $this->assertStringContainsString("group('auth', ['filter' => 'csrf,tenantContext']", $routes);
    }

    public function testProtectedAuthRedirectUsesShieldRoute(): void
    {
        $filter = file_get_contents(APPPATH . 'Filters/ProtectedAuthFilter.php');

        $this->assertStringContainsString("site_url('auth/login')", $filter);
        $this->assertStringNotContainsString("to('/login')", $filter);
    }
}
