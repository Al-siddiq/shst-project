<?php

namespace App\Services\Admissions;

use App\Libraries\Auth\IdentityGuard;
use App\Models\Tenant\Admissions\AdmissionDecisionModel;
use App\Models\Tenant\Admissions\AdmissionOfferModel;
use App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel;
use App\Models\Tenant\Admissions\AdmissionShortlistBatchModel;
use App\Models\Tenant\Admissions\AdmissionShortlistEntryModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Owns Phase 5 shortlisting, decision transitions, offer issuance, and offer visibility. */
class AdmissionDecisionService
{
    private const BATCH_TYPES = ['shortlist', 'waitlist', 'rejection'];
    private const ENTRY_STATUSES = ['shortlisted', 'waitlisted', 'rejected'];
    private const DECISION_TYPES = ['shortlisted', 'waitlisted', 'rejected', 'offered'];
    private const ACTIVE_OFFER_STATUSES = ['issued', 'pending_acceptance'];
    private const DECISION_SOURCE_STATUSES = ['screened', 'reviewed', 'screening_pending', 'shortlisted', 'waitlisted', 'rejected', 'offered'];

    /** @return array<string, mixed> */
    public function workspace(): array
    {
        $this->assertAuthority('admissions.decisions.manage');

        return [
            'batches' => (new AdmissionShortlistBatchModel())->orderBy('created_at', 'DESC')->findAll(50),
            'decisions' => (new AdmissionDecisionModel())->where('is_current', 1)->orderBy('decided_at', 'DESC')->findAll(100),
            'offers' => (new AdmissionOfferModel())->orderBy('issued_at', 'DESC')->findAll(100),
            'metrics' => $this->metrics(),
        ];
    }

    public function createBatch(array $payload): int
    {
        $this->assertAuthority('admissions.shortlist.manage');
        $type = (string) ($payload['batch_type'] ?? 'shortlist');
        if (! in_array($type, self::BATCH_TYPES, true)) {
            throw new InvalidArgumentException('Unsupported shortlist batch type.');
        }

        $id = (int) (new AdmissionShortlistBatchModel())->insert([
            'admission_cycle_id' => (int) ($payload['admission_cycle_id'] ?? 0),
            'programme_opening_id' => empty($payload['programme_opening_id']) ? null : (int) $payload['programme_opening_id'],
            'batch_code' => strtoupper(trim((string) ($payload['batch_code'] ?? ''))),
            'title' => trim((string) ($payload['title'] ?? '')),
            'batch_type' => $type,
            'status' => 'draft',
            'private_notes' => trim((string) ($payload['private_notes'] ?? '')) ?: null,
        ], true);
        service('auditLogger')->record('admissions.shortlist.batch_created', ['target_type' => 'admission_shortlist_batch', 'target_id' => $id, 'summary' => 'Admissions staff created a decision batch.', 'metadata' => ['batch_type' => $type]]);

        return $id;
    }

    public function addToBatch(int $batchId, int $applicationId, array $payload = []): int
    {
        $this->assertAuthority('admissions.shortlist.manage');
        $batch = (new AdmissionShortlistBatchModel())->find($batchId);
        $application = $this->application($applicationId);
        if ($batch === null || (int) $batch['admission_cycle_id'] !== (int) $application['admission_cycle_id']) {
            throw new InvalidArgumentException('Shortlist batch and application must belong to the same tenant cycle.');
        }
        $entryStatus = (string) ($payload['entry_status'] ?? $this->entryStatusForBatch((string) $batch['batch_type']));
        if (! in_array($entryStatus, self::ENTRY_STATUSES, true)) {
            throw new InvalidArgumentException('Unsupported shortlist entry status.');
        }

        $entryModel = new AdmissionShortlistEntryModel();
        $existing = $entryModel->where('shortlist_batch_id', $batchId)->where('applicant_application_id', $applicationId)->first();
        $data = [
            'shortlist_batch_id' => $batchId,
            'applicant_application_id' => $applicationId,
            'entry_status' => $entryStatus,
            'rank_position' => empty($payload['rank_position']) ? null : (int) $payload['rank_position'],
            'private_notes' => trim((string) ($payload['private_notes'] ?? '')) ?: null,
        ];
        $id = $existing === null ? (int) $entryModel->insert($data, true) : (int) $existing['id'];
        if ($existing !== null) {
            $entryModel->update($id, $data);
        }
        (new ApplicantApplicationModel())->update($applicationId, ['status' => $entryStatus]);
        service('auditLogger')->record('admissions.shortlist.entry_saved', ['target_type' => 'applicant_application', 'target_id' => $applicationId, 'summary' => 'Admissions staff added an application to a decision batch.', 'metadata' => ['entry_status' => $entryStatus, 'batch_id' => $batchId]]);

        return $id;
    }

    public function decide(int $applicationId, array $payload): int
    {
        $this->assertAuthority('admissions.decisions.manage');
        $application = $this->application($applicationId);
        $decisionType = (string) ($payload['decision_type'] ?? '');
        if (! in_array($decisionType, self::DECISION_TYPES, true)) {
            throw new InvalidArgumentException('Unsupported admission decision.');
        }

        $requiresApproval = ! empty($payload['requires_approval']);
        $canApprove = $this->can('admissions.decisions.approve');
        $status = $decisionType === 'offered' && $requiresApproval && ! $canApprove ? 'pending_approval' : 'approved';
        $now = Time::now()->toDateTimeString();
        $decisionModel = new AdmissionDecisionModel();
        foreach ($decisionModel->where('applicant_application_id', $applicationId)->where('is_current', 1)->findAll() as $current) {
            $decisionModel->update((int) $current['id'], ['is_current' => 0]);
        }

        $decisionId = (int) $decisionModel->insert([
            'applicant_application_id' => $applicationId,
            'decision_type' => $decisionType,
            'decision_status' => $status,
            'is_current' => 1,
            'requires_approval' => $requiresApproval ? 1 : 0,
            'decided_by' => (new IdentityGuard())->userId(),
            'approved_by' => $status === 'approved' ? (new IdentityGuard())->userId() : null,
            'decision_reason' => trim((string) ($payload['decision_reason'] ?? '')) ?: null,
            'private_notes' => trim((string) ($payload['private_notes'] ?? '')) ?: null,
            'decided_at' => $now,
            'approved_at' => $status === 'approved' ? $now : null,
        ], true);

        $newApplicationStatus = $decisionType === 'offered' && $status === 'approved' ? 'offered' : $decisionType;
        (new ApplicantApplicationModel())->update($applicationId, ['status' => $newApplicationStatus]);
        service('auditLogger')->record('admissions.decision.recorded', ['target_type' => 'applicant_application', 'target_id' => $applicationId, 'summary' => 'Admissions staff recorded an admission decision.', 'metadata' => ['decision_type' => $decisionType, 'decision_status' => $status]]);
        if ($decisionType === 'offered' && $status === 'approved') {
            $this->issueOffer($decisionId, $application, $payload);
        }
        service('admissionNotificationDispatcher')->queue('admissions.decision.recorded', null, ['application_id' => $applicationId, 'decision_type' => $decisionType, 'decision_status' => $status]);

        return $decisionId;
    }

    public function approveDecision(int $decisionId, array $payload = []): int
    {
        $this->assertAuthority('admissions.decisions.approve');
        $decision = (new AdmissionDecisionModel())->find($decisionId);
        if ($decision === null || $decision['decision_type'] !== 'offered') {
            throw new InvalidArgumentException('Only pending offer decisions can be approved.');
        }
        if ($decision['decision_status'] === 'approved') {
            return (int) $decision['id'];
        }

        $application = $this->application((int) $decision['applicant_application_id']);
        $now = Time::now()->toDateTimeString();
        (new AdmissionDecisionModel())->update($decisionId, ['decision_status' => 'approved', 'approved_by' => (new IdentityGuard())->userId(), 'approved_at' => $now]);
        (new ApplicantApplicationModel())->update((int) $application['id'], ['status' => 'offered']);
        $this->issueOffer($decisionId, $application, $payload);
        service('auditLogger')->record('admissions.decision.approved', ['target_type' => 'admission_decision', 'target_id' => $decisionId, 'summary' => 'Admissions staff approved an offer decision.']);

        return $decisionId;
    }

    /** @return array<string, mixed>|null */
    public function currentOfferForApplicant(?int $applicationId = null): ?array
    {
        $profile = service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current());
        if ($profile === null) {
            return null;
        }
        $applications = (new ApplicantApplicationModel())->where('applicant_profile_id', $profile['id'])->findAll();
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $applications);
        if ($applicationId !== null && ! in_array($applicationId, $ids, true)) {
            return null;
        }
        $ids = $applicationId === null ? $ids : [$applicationId];
        if ($ids === []) {
            return null;
        }

        return (new AdmissionOfferModel())->whereIn('applicant_application_id', $ids)->whereIn('offer_status', self::ACTIVE_OFFER_STATUSES)->orderBy('issued_at', 'DESC')->first();
    }

    /** @return array<string, int> */
    public function metrics(): array
    {
        $this->assertAuthority('admissions.decisions.manage');

        return [
            'shortlisted' => (new ApplicantApplicationModel())->where('status', 'shortlisted')->countAllResults(),
            'waitlisted' => (new ApplicantApplicationModel())->where('status', 'waitlisted')->countAllResults(),
            'rejected' => (new ApplicantApplicationModel())->where('status', 'rejected')->countAllResults(),
            'offered' => (new ApplicantApplicationModel())->where('status', 'offered')->countAllResults(),
            'pending_approval' => (new AdmissionDecisionModel())->where('decision_status', 'pending_approval')->where('is_current', 1)->countAllResults(),
        ];
    }

    /** @param array<string, mixed> $application @param array<string, mixed> $payload */
    private function issueOffer(int $decisionId, array $application, array $payload): int
    {
        $offerModel = new AdmissionOfferModel();
        $existing = $offerModel->where('applicant_application_id', $application['id'])->whereIn('offer_status', self::ACTIVE_OFFER_STATUSES)->first();
        if ($existing !== null) {
            return (int) $existing['id'];
        }
        $opening = (new AdmissionProgrammeOpeningModel())->find((int) $application['programme_opening_id']);
        $issuedAt = Time::now();
        $expiresAt = $issuedAt->addDays(max(1, min(90, (int) ($payload['offer_valid_days'] ?? 14))))->toDateTimeString();
        $reference = service('admissionReferenceGenerator')->next('offer');
        $snapshot = ['application' => $application, 'programme_opening' => $opening, 'offer_reference' => $reference];
        $offerId = (int) $offerModel->insert([
            'applicant_application_id' => $application['id'],
            'admission_decision_id' => $decisionId,
            'offer_reference' => $reference,
            'offer_status' => 'issued',
            'offered_programme_opening_id' => $application['programme_opening_id'],
            'offer_snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_SLASHES),
            'offer_letter_template_key' => trim((string) ($payload['offer_letter_template_key'] ?? 'default')) ?: 'default',
            'issued_at' => $issuedAt->toDateTimeString(),
            'expires_at' => $expiresAt,
        ], true);
        service('auditLogger')->record('admissions.offer.issued', ['target_type' => 'admission_offer', 'target_id' => $offerId, 'summary' => 'Admissions staff issued an admission offer.', 'metadata' => ['offer_reference' => $reference]]);
        service('admissionNotificationDispatcher')->queue('admissions.offer.issued', null, ['application_id' => $application['id'], 'offer_reference' => $reference]);

        return $offerId;
    }

    /** @return array<string, mixed> */
    private function application(int $applicationId): array
    {
        $application = (new ApplicantApplicationModel())->find($applicationId);
        if ($application === null || ! in_array($application['status'], self::DECISION_SOURCE_STATUSES, true)) {
            throw new InvalidArgumentException('Application is not eligible for Phase 5 decisions.');
        }

        return $application;
    }

    private function entryStatusForBatch(string $type): string
    {
        return match ($type) {
            'waitlist' => 'waitlisted',
            'rejection' => 'rejected',
            default => 'shortlisted',
        };
    }

    private function assertAuthority(string $authority): void
    {
        if (! $this->can($authority)) {
            throw new InvalidArgumentException('You do not have authority to manage admission decisions.');
        }
    }

    private function can(string $authority): bool
    {
        $context = service('tenantContextManager')->current();

        return service('tenantAccess')->isMember($context) && service('tenantAccess')->hasAuthority($context, $authority);
    }
}
