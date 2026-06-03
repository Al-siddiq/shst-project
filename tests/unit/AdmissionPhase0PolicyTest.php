<?php

use App\Services\Admissions\AdmissionAuthorityCatalog;
use App\Services\Admissions\ApplicantDocumentStorage;
use App\Services\Admissions\ApplicantIdentityNormalizer;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Admissions;
use Config\Security;

/** Guards the approved Phase 0 admissions security policy. @internal */
final class AdmissionPhase0PolicyTest extends CIUnitTestCase
{
    public function testAdmissionAuthorityCatalogContainsRequiredCapabilities(): void
    {
        $this->assertSame([
            'admissions.dashboard.view', 'admissions.cycles.manage', 'admissions.programmes.manage',
            'admissions.requirements.manage', 'admissions.applications.view', 'admissions.applications.review',
            'admissions.documents.review', 'admissions.screening.manage', 'admissions.shortlist.manage',
            'admissions.decisions.manage', 'admissions.decisions.approve', 'admissions.lists.preview',
            'admissions.lists.publish', 'admissions.acceptance.view', 'admissions.clearance.manage',
            'admissions.reports.view', 'admissions.audit.view',
        ], array_keys(AdmissionAuthorityCatalog::AUTHORITIES));
    }

    /** @dataProvider validIdentifierProvider */
    public function testApplicantIdentifiersNormalizeForShieldLookup(string $input, string $type, string $expected): void
    {
        $this->assertSame(['type' => $type, 'value' => $expected], (new ApplicantIdentityNormalizer())->normalize($input));
    }

    /** @return list<array{string, string, string}> */
    public static function validIdentifierProvider(): array
    {
        return [
            ['Applicant@Example.COM', 'email', 'applicant@example.com'],
            ['0803 123 4567', 'phone', '+2348031234567'],
            ['2348031234567', 'phone', '+2348031234567'],
            ['+234-803-123-4567', 'phone', '+2348031234567'],
        ];
    }

    public function testInvalidNigerianPhoneFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ApplicantIdentityNormalizer())->normalize('12345');
    }

    public function testApplicantEntryAndProtectedRouteBoundariesAreRegistered(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $this->assertStringContainsString("\$routes->get('apply', 'PublicSite\\ApplicantStartController::index');", $routes);
        $this->assertStringContainsString("'filter' => 'protectedAuth,tenantContext:required,applicantAccess'", $routes);
        $this->assertStringContainsString("service('auth')->routes(\$routes);", $routes);
    }

    public function testCsrfTokensAreRandomizedForBrowserMutationProtection(): void
    {
        $this->assertTrue(config(Security::class)->tokenRandomize);
    }

    public function testApplicantDocumentPolicyIsPrivateAndTraversalSafe(): void
    {
        $config = config(Admissions::class);
        $this->assertSame('uploads/admissions', $config->documentStorageDirectory);
        $this->assertSame(['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'], $config->documentExtensionsByMimeType);

        $this->expectException(RuntimeException::class);
        (new ApplicantDocumentStorage())->privateFile(['storage_path' => '../private.pdf']);
    }
}
