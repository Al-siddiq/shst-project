<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class Phase6OperationalContractTest extends CIUnitTestCase
{
    public function testHealthRoutesAndGlobalSecurityTelemetryAreConfigured(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $filters = file_get_contents(APPPATH . 'Config/Filters.php');
        $this->assertStringContainsString("health/live", $routes);
        $this->assertStringContainsString("health/ready", $routes);
        $this->assertStringContainsString("'secureheaders'", $filters);
        $this->assertStringContainsString("'requestTelemetry'", $filters);
    }

    public function testReleaseGateKeepsBlockFourBlocked(): void
    {
        $gate = file_get_contents(ROOTPATH . 'docs/stabilization/phase_6_release_gate.md');
        $this->assertStringContainsString('Block 4 remains blocked', $gate);
        $this->assertStringContainsString('RPO ≤15 minutes', $gate);
        $this->assertStringContainsString('RTO ≤4 hours', $gate);
    }
}
