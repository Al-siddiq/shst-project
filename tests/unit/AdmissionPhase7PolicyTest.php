<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/** Protects Phase 7 acceptance/clearance and handoff boundaries. @internal */
final class AdmissionPhase7PolicyTest extends CIUnitTestCase
{
    public function testAcceptanceRoutesProtectApplicantAndStaffActions(): void
    {
        $routes = file_get_contents(ROOTPATH . 'app/Config/Routes.php');
        $this->assertStringContainsString('applicant/offers/accept', $routes);
        $this->assertStringContainsString('applicant/offers/decline', $routes);
        $this->assertStringContainsString('tenant/admissions/acceptance', $routes);
        $this->assertStringContainsString('admissions.acceptance.view', $routes);
        $this->assertStringContainsString('admissions.clearance.manage', $routes);
    }

    public function testAcceptanceServiceCreatesMarkerWithoutStudentRecords(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionAcceptanceService.php');
        $this->assertStringContainsString('acceptOwnOffer', $service);
        $this->assertStringContainsString('declineOwnOffer', $service);
        $this->assertStringContainsString('eligible_for_student_conversion', $service);
        $this->assertStringNotContainsString('StudentModel', $service);
        $this->assertStringNotContainsString('createStudent', $service);
    }

    public function testPaymentEngineRemainsPlaceholderOnly(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionAcceptanceService.php');
        $this->assertStringContainsString('acceptance_fee_status', $service);
        $this->assertStringNotContainsString('paymentGateway', $service);
        $this->assertStringNotContainsString('receipt', strtolower($service));
        $this->assertStringNotContainsString('ledger', strtolower($service));
    }
}
