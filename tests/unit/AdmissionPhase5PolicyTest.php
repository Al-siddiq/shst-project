<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/** Protects Phase 5 decision and offer boundaries. @internal */
final class AdmissionPhase5PolicyTest extends CIUnitTestCase
{
    public function testDecisionRoutesRequireDecisionAuthorities(): void
    {
        $routes = file_get_contents(ROOTPATH . 'app/Config/Routes.php');
        $this->assertStringContainsString('tenant/admissions/decisions', $routes);
        $this->assertStringContainsString('admissions.decisions.manage', $routes);
        $this->assertStringContainsString('admissions.shortlist.manage', $routes);
        $this->assertStringContainsString('admissions.decisions.approve', $routes);
        $this->assertStringContainsString('applicant/offers', $routes);
    }

    public function testDecisionServiceEnforcesCurrentDecisionAndActiveOfferRules(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionDecisionService.php');
        $this->assertStringContainsString("where('is_current', 1)", $service);
        $this->assertStringContainsString('ACTIVE_OFFER_STATUSES', $service);
        $this->assertStringContainsString('offer_snapshot_json', $service);
        $this->assertStringContainsString('admissions.offer.issued', $service);
    }

    public function testPublicationAcceptanceAndConversionRemainOutOfScope(): void
    {
        $service = file_get_contents(ROOTPATH . 'app/Services/Admissions/AdmissionDecisionService.php');
        $this->assertStringNotContainsString('publishAdmissionList', $service);
        $this->assertStringNotContainsString('acceptOffer', $service);
        $this->assertStringNotContainsString('clearance', strtolower($service));
        $this->assertStringNotContainsString('convertToStudent', $service);
    }
}
