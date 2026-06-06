<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/** Phase 8 policy checks for reports, operations, and stabilization boundaries. */
final class AdmissionPhase8PolicyTest extends CIUnitTestCase
{
    public function testRoutesRequireSeparatedReportAndAuditAuthorities(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');

        $this->assertStringContainsString('tenant/admissions/reports', $routes);
        $this->assertStringContainsString('tenantAccess:authority:admissions.reports.view', $routes);
        $this->assertStringContainsString('Tenant\\Admissions\\ReportController::export/$1', $routes);
        $this->assertStringContainsString('tenant/admissions/operations', $routes);
        $this->assertStringContainsString('tenantAccess:authority:admissions.audit.view', $routes);
        $this->assertStringContainsString('OperationsController::retryOutbox/$1', $routes);
        $this->assertStringContainsString("'filter' => 'csrf'", $routes);
    }

    public function testServicesAreRegisteredForPhase8Only(): void
    {
        $services = file_get_contents(APPPATH . 'Config/Services.php');

        $this->assertStringContainsString('AdmissionReportService', $services);
        $this->assertStringContainsString('AdmissionOperationsService', $services);
        $this->assertStringContainsString('admissionReport', $services);
        $this->assertStringContainsString('admissionOperations', $services);
    }

    public function testPhase8DocumentationKeepsLaterBlockBoundaries(): void
    {
        $doc = file_get_contents(ROOTPATH . 'docs/block_03_phase_8_reports_operations_stabilization.md');

        $this->assertStringContainsString('Safe CSV exports', $doc);
        $this->assertStringContainsString('Manual mobile verification', $doc);
        $this->assertStringContainsString('Public website integration verification', $doc);
        $this->assertStringContainsString('Performance review', $doc);
        $this->assertStringContainsString('does not implement the payment engine', $doc);
        $this->assertStringContainsString('student conversion', $doc);
    }
}
