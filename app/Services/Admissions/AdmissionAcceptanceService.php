<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionClearanceStatusModel;
use App\Models\Tenant\Admissions\AdmissionConversionEligibilityMarkerModel;
use App\Models\Tenant\Admissions\AdmissionOfferAcceptanceModel;
use App\Models\Tenant\Admissions\AdmissionOfferModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Owns Phase 7 offer acceptance, clearance placeholder, and Block 5 handoff markers. */
class AdmissionAcceptanceService
{
    private const ACCEPTABLE_OFFER_STATUSES = ['issued', 'pending_acceptance'];
    private const ACCEPTANCE_FEE_STATUSES = ['not_required', 'pending_external_confirmation', 'externally_confirmed', 'waived'];
    private const CLEARANCE_STATUSES = ['pending', 'in_progress', 'cleared', 'not_cleared'];
    private const ELIGIBLE_FEE_STATUSES = ['not_required', 'externally_confirmed', 'waived'];

    /** @return array<string, mixed> */
    public function applicantOffer(): array
    {
        $offer = $this->ownedApplicantOffer();
        if ($offer === null) {
            throw new InvalidArgumentException('No admission offer was found for this applicant.');
        }

        return $this->offerState($offer);
    }

    /** @return array<string, mixed> */
    public function acceptOwnOffer(array $payload = []): array
    {
        $state = $this->applicantOffer();
        $offer = $state['offer'];
        if (! in_array($offer['offer_status'], self::ACCEPTABLE_OFFER_STATUSES, true)) {
            return $state;
        }

        $now = Time::now()->toDateTimeString();
        $acceptance = $this->upsertAcceptance($offer, [
            'acceptance_status' => 'accepted',
            'acceptance_fee_status' => 'not_required',
            'accepted_at' => $now,
            'declined_at' => null,
            'applicant_comment' => trim((string) ($payload['applicant_comment'] ?? '')) ?: null,
        ]);
        (new AdmissionOfferModel())->update((int) $offer['id'], ['offer_status' => 'accepted']);
        (new ApplicantApplicationModel())->update((int) $offer['applicant_application_id'], ['status' => 'accepted']);
        $this->ensureClearancePlaceholder($offer, 'not_required');
        service('auditLogger')->record('admissions.offer.accepted', ['target_type' => 'admission_offer', 'target_id' => $offer['id'], 'summary' => 'Applicant accepted an admission offer.']);
        service('admissionNotificationDispatcher')->queue('admissions.offer.accepted', null, ['offer_id' => $offer['id'], 'application_id' => $offer['applicant_application_id']]);

        return array_merge($this->offerState((new AdmissionOfferModel())->find((int) $offer['id'])), ['acceptance' => $acceptance]);
    }

    /** @return array<string, mixed> */
    public function declineOwnOffer(array $payload = []): array
    {
        $state = $this->applicantOffer();
        $offer = $state['offer'];
        if ($offer['offer_status'] === 'declined') {
            return $state;
        }

        $now = Time::now()->toDateTimeString();
        $acceptance = $this->upsertAcceptance($offer, [
            'acceptance_status' => 'declined',
            'acceptance_fee_status' => 'not_required',
            'accepted_at' => null,
            'declined_at' => $now,
            'applicant_comment' => trim((string) ($payload['applicant_comment'] ?? '')) ?: null,
        ]);
        (new AdmissionOfferModel())->update((int) $offer['id'], ['offer_status' => 'declined']);
        (new ApplicantApplicationModel())->update((int) $offer['applicant_application_id'], ['status' => 'declined']);
        service('auditLogger')->record('admissions.offer.declined', ['target_type' => 'admission_offer', 'target_id' => $offer['id'], 'summary' => 'Applicant declined an admission offer.']);
        service('admissionNotificationDispatcher')->queue('admissions.offer.declined', null, ['offer_id' => $offer['id'], 'application_id' => $offer['applicant_application_id']]);

        return array_merge($this->offerState((new AdmissionOfferModel())->find((int) $offer['id'])), ['acceptance' => $acceptance]);
    }

    /** @return array<string, mixed> */
    public function staffWorkspace(): array
    {
        $this->assertAuthority('admissions.acceptance.view');

        return [
            'acceptances' => (new AdmissionOfferAcceptanceModel())->orderBy('updated_at', 'DESC')->findAll(100),
            'clearances' => (new AdmissionClearanceStatusModel())->orderBy('updated_at', 'DESC')->findAll(100),
            'eligibleMarkers' => (new AdmissionConversionEligibilityMarkerModel())->orderBy('marked_at', 'DESC')->findAll(100),
            'metrics' => $this->metrics(),
        ];
    }

    public function updateClearance(int $applicationId, array $payload): int
    {
        $this->assertAuthority('admissions.clearance.manage');
        $offer = (new AdmissionOfferModel())->where('applicant_application_id', $applicationId)->where('offer_status', 'accepted')->first();
        if ($offer === null) {
            throw new InvalidArgumentException('Only accepted offers can receive clearance updates.');
        }
        $status = (string) ($payload['clearance_status'] ?? 'pending');
        $feeStatus = (string) ($payload['acceptance_fee_status'] ?? 'not_required');
        if (! in_array($status, self::CLEARANCE_STATUSES, true) || ! in_array($feeStatus, self::ACCEPTANCE_FEE_STATUSES, true)) {
            throw new InvalidArgumentException('Unsupported clearance or acceptance-fee placeholder status.');
        }

        $model = new AdmissionClearanceStatusModel();
        $existing = $model->where('applicant_application_id', $applicationId)->first();
        $payload = [
            'applicant_application_id' => $applicationId,
            'admission_offer_id' => $offer['id'],
            'clearance_status' => $status,
            'acceptance_fee_status' => $feeStatus,
            'staff_notes' => trim((string) ($payload['staff_notes'] ?? '')) ?: null,
            'updated_by_staff_id' => service('tenantAccess')->currentUserId(),
            'cleared_at' => $status === 'cleared' ? Time::now()->toDateTimeString() : null,
        ];
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) {
            $model->update($id, $payload);
        }
        service('auditLogger')->record('admissions.clearance.updated', ['target_type' => 'applicant_application', 'target_id' => $applicationId, 'summary' => 'Admissions staff updated clearance placeholder status.', 'metadata' => ['clearance_status' => $status, 'acceptance_fee_status' => $feeStatus]]);

        return $id;
    }

    public function markEligible(int $applicationId): int
    {
        $this->assertAuthority('admissions.clearance.manage');
        $offer = (new AdmissionOfferModel())->where('applicant_application_id', $applicationId)->where('offer_status', 'accepted')->first();
        $clearance = (new AdmissionClearanceStatusModel())->where('applicant_application_id', $applicationId)->first();
        if ($offer === null || $clearance === null || $clearance['clearance_status'] !== 'cleared' || ! in_array($clearance['acceptance_fee_status'], self::ELIGIBLE_FEE_STATUSES, true)) {
            throw new InvalidArgumentException('Eligibility marker requires accepted offer, cleared status, and satisfied acceptance-fee placeholder.');
        }

        $model = new AdmissionConversionEligibilityMarkerModel();
        $existing = $model->where('applicant_application_id', $applicationId)->first();
        if ($existing !== null) {
            return (int) $existing['id'];
        }
        $application = (new ApplicantApplicationModel())->find($applicationId);
        $now = Time::now()->toDateTimeString();
        $id = (int) $model->insert([
            'applicant_application_id' => $applicationId,
            'admission_offer_id' => $offer['id'],
            'eligibility_status' => 'eligible_for_student_conversion',
            'marked_at' => $now,
            'handoff_payload_json' => json_encode(['application' => $application, 'offer_id' => $offer['id'], 'clearance_id' => $clearance['id']], JSON_UNESCAPED_SLASHES),
        ], true);
        (new ApplicantApplicationModel())->update($applicationId, ['status' => 'eligible_for_student_conversion']);
        service('auditLogger')->record('admissions.conversion_eligibility.marked', ['target_type' => 'applicant_application', 'target_id' => $applicationId, 'summary' => 'Admissions staff marked applicant eligible for Block 5 student conversion.']);

        return $id;
    }

    /** @return array<string, int> */
    public function metrics(): array
    {
        $this->assertAuthority('admissions.acceptance.view');

        return [
            'accepted' => (new AdmissionOfferAcceptanceModel())->where('acceptance_status', 'accepted')->countAllResults(),
            'declined' => (new AdmissionOfferAcceptanceModel())->where('acceptance_status', 'declined')->countAllResults(),
            'cleared' => (new AdmissionClearanceStatusModel())->where('clearance_status', 'cleared')->countAllResults(),
            'eligible' => (new AdmissionConversionEligibilityMarkerModel())->countAllResults(),
        ];
    }

    /** @return array<string, mixed>|null */
    private function ownedApplicantOffer(): ?array
    {
        $profile = service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current());
        if ($profile === null) {
            return null;
        }
        $applications = (new ApplicantApplicationModel())->where('applicant_profile_id', $profile['id'])->findAll();
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $applications);
        if ($ids === []) {
            return null;
        }

        return (new AdmissionOfferModel())->whereIn('applicant_application_id', $ids)->orderBy('issued_at', 'DESC')->first();
    }

    /** @param array<string, mixed> $offer @return array<string, mixed> */
    private function offerState(array $offer): array
    {
        return [
            'offer' => $offer,
            'snapshot' => json_decode((string) ($offer['offer_snapshot_json'] ?? '{}'), true) ?: [],
            'acceptance' => (new AdmissionOfferAcceptanceModel())->where('admission_offer_id', $offer['id'])->first(),
            'clearance' => (new AdmissionClearanceStatusModel())->where('admission_offer_id', $offer['id'])->first(),
            'eligibility' => (new AdmissionConversionEligibilityMarkerModel())->where('admission_offer_id', $offer['id'])->first(),
        ];
    }

    /** @param array<string,mixed> $offer @param array<string,mixed> $payload @return array<string,mixed> */
    private function upsertAcceptance(array $offer, array $payload): array
    {
        $model = new AdmissionOfferAcceptanceModel();
        $existing = $model->where('admission_offer_id', $offer['id'])->first();
        $payload = array_merge($payload, ['applicant_application_id' => $offer['applicant_application_id'], 'admission_offer_id' => $offer['id']]);
        $id = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) {
            $model->update($id, $payload);
        }

        return $model->find($id);
    }

    /** @param array<string,mixed> $offer */
    private function ensureClearancePlaceholder(array $offer, string $feeStatus): void
    {
        $model = new AdmissionClearanceStatusModel();
        if ($model->where('applicant_application_id', $offer['applicant_application_id'])->first() !== null) {
            return;
        }
        $model->insert(['applicant_application_id' => $offer['applicant_application_id'], 'admission_offer_id' => $offer['id'], 'clearance_status' => 'pending', 'acceptance_fee_status' => $feeStatus]);
    }

    private function assertAuthority(string $authority): void
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context) || ! service('tenantAccess')->hasAuthority($context, $authority)) {
            throw new InvalidArgumentException('You do not have authority to manage admission acceptance or clearance.');
        }
    }
}
