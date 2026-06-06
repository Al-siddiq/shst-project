<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/** Protects Phase 6 publication and public-output boundaries. @internal */
final class AdmissionPhase6PolicyTest extends CIUnitTestCase
{
    public function testListRoutesSeparatePreviewPublishAndPublicAccess(): void
    {
        $routes = file_get_contents(ROOTPATH . 'app/Config/Routes.php');
        $this->assertStringContainsString('tenant/admissions/lists', $routes);
        $this->assertStringContainsString('admissions.lists.preview', $routes);
        $this->assertStringContainsString('admissions.lists.publish', $routes);
        $this->assertStringContainsString('admissions/lists/(:segment)', $routes);
    }

    public function testPublicationServiceAllowsSafeFieldsOnly(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionListPublicationService.php');
        $this->assertStringContainsString('SAFE_PUBLIC_FIELDS', $service);
        $this->assertStringContainsString('application_number', $service);
        $this->assertStringContainsString('applicant_display_name', $service);
        $this->assertStringContainsString('programme_name', $service);
        $this->assertStringNotContainsString('phone_e164', $service);
        $this->assertStringNotContainsString('residential_address', $service);
    }

    public function testAcceptanceClearanceAndConversionRemainOutOfScope(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionListPublicationService.php');
        $this->assertStringNotContainsString('acceptOffer', $service);
        $this->assertStringNotContainsString('clearance', strtolower($service));
        $this->assertStringNotContainsString('convertToStudent', $service);
    }
}
