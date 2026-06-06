<?php

use App\Models\Tenant\Admissions\ApplicantProfileModel;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use App\Models\OperationalAuthorityModel;
use App\Models\MembershipAuthorityModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Admissions\AdmissionContextHelper;

/** Proves Phase 0 applicant identity and document boundaries remain tenant safe. @internal */
final class AdmissionPhase0IsolationTest extends CIUnitTestCase
{
    use AdmissionContextHelper;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testApplicantProfilesAndDocumentsCannotCrossTenantContext(): void
    {
        $tenantA = $this->createAdmissionTenant('admission-a');
        $tenantB = $this->createAdmissionTenant('admission-b');
        $this->authenticateAdmissionUser(701);
        $this->useAdmissionTenant($tenantA, 'admission-a');
        $profileId = (int) (new ApplicantProfileModel())->insert(['user_id' => 701, 'phone_e164' => '+2348031234567'], true);
        $documentId = (int) (new ApplicationDocumentModel())->insert($this->documentData($profileId, 'tenant-a-token'), true);

        $this->assertNotNull(service('applicantAccessPolicy')->ownedDocument(service('tenantContextManager')->current(), 'tenant-a-token'));

        $this->useAdmissionTenant($tenantB, 'admission-b');
        $this->assertNull((new ApplicantProfileModel())->find($profileId));
        $this->assertNull((new ApplicationDocumentModel())->find($documentId));
        $this->assertNull(service('applicantAccessPolicy')->ownedDocument(service('tenantContextManager')->current(), 'tenant-a-token'));
    }

    public function testAuthenticatedApplicantCannotResolveAnotherApplicantsDocument(): void
    {
        $tenant = $this->createAdmissionTenant('admission-owner');
        $this->useAdmissionTenant($tenant, 'admission-owner');
        $this->authenticateAdmissionUser(801);
        $ownerId = (int) (new ApplicantProfileModel())->insert(['user_id' => 801, 'email' => 'owner@example.com'], true);
        (new ApplicationDocumentModel())->insert($this->documentData($ownerId, 'owner-token'));

        $this->authenticateAdmissionUser(802);
        (new ApplicantProfileModel())->insert(['user_id' => 802, 'email' => 'other@example.com']);

        $this->assertNull(service('applicantAccessPolicy')->ownedDocument(service('tenantContextManager')->current(), 'owner-token'));
    }

    public function testReviewerAuthorityRequiresActiveTenantMembership(): void
    {
        $tenantA = $this->createAdmissionTenant('review-a');
        $tenantB = $this->createAdmissionTenant('review-b');
        $this->useAdmissionTenant($tenantA, 'review-a');
        $this->createStaffContext($tenantA, 901);
        $authorityId = (int) (new OperationalAuthorityModel())->insert(['code' => 'admissions.documents.review', 'name' => 'Review documents', 'is_active' => 1], true);
        (new MembershipAuthorityModel())->insert(['user_id' => 901, 'authority_id' => $authorityId]);

        $this->assertTrue(service('tenantAccess')->isMember(service('tenantContextManager')->current()));
        $this->assertTrue(service('tenantAccess')->hasAuthority(service('tenantContextManager')->current(), 'admissions.documents.review'));

        $this->useAdmissionTenant($tenantB, 'review-b');
        $this->assertFalse(service('tenantAccess')->isMember(service('tenantContextManager')->current()));
        $this->assertFalse(service('tenantAccess')->hasAuthority(service('tenantContextManager')->current(), 'admissions.documents.review'));
    }

    public function testReferenceSequencesAreSeparatedByTenant(): void
    {
        $tenantA = $this->createAdmissionTenant('reference-a');
        $tenantB = $this->createAdmissionTenant('reference-b');
        $this->useAdmissionTenant($tenantA, 'reference-a');
        $this->assertSame('APP-' . $tenantA . '-000001', service('admissionReferenceGenerator')->next());
        $this->assertSame('APP-' . $tenantA . '-000002', service('admissionReferenceGenerator')->next());

        $this->useAdmissionTenant($tenantB, 'reference-b');
        $this->assertSame('APP-' . $tenantB . '-000001', service('admissionReferenceGenerator')->next());
    }

    /** @return array<string, mixed> */
    private function documentData(int $profileId, string $token): array
    {
        return [
            'applicant_profile_id' => $profileId,
            'public_token' => $token,
            'document_type' => 'passport',
            'original_name' => 'passport.jpg',
            'storage_path' => 'tenant/passport.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 12,
            'checksum_sha256' => str_repeat('a', 64),
        ];
    }
}
