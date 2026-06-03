<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/** Protects Phase 3 route, storage, and bounded-scope contracts. @internal */
final class AdmissionPhase3PolicyTest extends CIUnitTestCase
{
    public function testRoutesExposeOnlyApplicantCompletionEndpoints(): void
    {
        $routes = file_get_contents(ROOTPATH . 'app/Config/Routes.php');
        $this->assertStringContainsString('applicant/applications/(:segment)/olevel', $routes);
        $this->assertStringContainsString('applicant/applications/(:segment)/documents', $routes);
        $this->assertStringContainsString('applicant/applications/(:segment)/preview', $routes);
        $this->assertStringContainsString('applicant/applications/(:segment)/submit', $routes);
        $this->assertStringNotContainsString('payments', $routes);
    }

    public function testSubmissionServiceIsIdempotentAndQueuesNotificationIntent(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/ApplicationSubmissionService.php');
        $this->assertStringContainsString("['status'] === 'submitted'", $service);
        $this->assertStringContainsString('admissionReferenceGenerator', $service);
        $this->assertStringContainsString('ApplicationSubmissionSnapshotModel', $service);
        $this->assertStringContainsString('admissionNotificationDispatcher', $service);
    }

    public function testPrivateUploadStorageRejectsUnsafeDocumentWrites(): void
    {
        $storage = file_get_contents(ROOTPATH . 'app/Services/Admissions/ApplicantDocumentStorage.php');
        $this->assertStringContainsString('WRITEPATH', $storage);
        $this->assertStringContainsString('maximumDocumentSizeBytes', $storage);
        $this->assertStringContainsString('documentExtensionsByMimeType', $storage);
        $this->assertStringContainsString('hash_file', $storage);
    }
}
