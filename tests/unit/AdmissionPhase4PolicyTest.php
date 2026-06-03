<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/** Protects Phase 4 review-workspace boundaries. @internal */
final class AdmissionPhase4PolicyTest extends CIUnitTestCase
{
    public function testReviewRoutesRequireTenantAuthority(): void
    {
        $routes = file_get_contents(ROOTPATH . 'app/Config/Routes.php');
        $this->assertStringContainsString('tenant/admissions/applications', $routes);
        $this->assertStringContainsString('admissions.applications.view', $routes);
        $this->assertStringContainsString('admissions.applications.review', $routes);
        $this->assertStringContainsString('admissions.documents.review', $routes);
        $this->assertStringContainsString('admissions.screening.manage', $routes);
    }

    public function testReviewServiceKeepsLaterPhasesOutOfScope(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionReviewService.php');
        $this->assertStringContainsString('reviewDocument', $service);
        $this->assertStringContainsString('recordScreening', $service);
        $this->assertStringNotContainsString('offerAdmission', $service);
        $this->assertStringNotContainsString('publishAdmissionList', $service);
        $this->assertStringNotContainsString('convertToStudent', $service);
    }

    public function testCorrectionWindowIsExplicitAndTemporary(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionCorrectionWindowService.php');
        $this->assertStringContainsString('correction_requested', $service);
        $this->assertStringContainsString('correction_allowed_until', $service);
        $this->assertStringContainsString('Time::now', $service);
    }
}
