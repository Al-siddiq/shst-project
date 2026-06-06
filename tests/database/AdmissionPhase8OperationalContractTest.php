<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;

/** Static operational contracts for Phase 8 tenant reports and outbox retries. */
final class AdmissionPhase8OperationalContractTest extends CIUnitTestCase
{
    public function testReportsUseTenantScopedModelsAndSafeCsvFields(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Admissions/AdmissionReportService.php');

        $this->assertStringContainsString('ApplicantApplicationModel', $service);
        $this->assertStringContainsString('AdmissionOfferAcceptanceModel', $service);
        $this->assertStringContainsString('AdmissionListPublicationModel', $service);
        $this->assertStringContainsString('csvExport', $service);
        $this->assertStringNotContainsString('private_notes', $service);
        $this->assertStringNotContainsString('storage_path', $service);
    }

    public function testOperationsExplicitlyScopesAuditLogsAndRequeuesOutbox(): void
    {
        $service = file_get_contents(APPPATH . 'Services/Admissions/AdmissionOperationsService.php');

        $this->assertStringContainsString('AdmissionNotificationOutboxModel', $service);
        $this->assertStringContainsString("->where('tenant_id', $context->tenantId)", $service);
        $this->assertStringContainsString("'status' => 'pending'", $service);
        $this->assertStringContainsString('admissions.outbox.retry_scheduled', $service);
        $this->assertStringContainsString('cannot be retried', $service);
    }
}
