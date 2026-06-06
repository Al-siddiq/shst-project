<?php

namespace App\Services\Admissions;

use App\Libraries\Auth\IdentityGuard;
use App\Models\AuditLogModel;
use App\Models\Tenant\Admissions\AdmissionApplicationReviewModel;
use App\Models\Tenant\Admissions\AdmissionDocumentReviewLogModel;
use App\Models\Tenant\Admissions\AdmissionScreeningRecordModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use App\Models\Tenant\Admissions\ApplicantProfileModel;
use App\Models\Tenant\Admissions\ApplicationBiodataDraftModel;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use App\Models\Tenant\Admissions\ApplicationOlevelResultModel;
use App\Models\Tenant\Admissions\ApplicationOlevelSittingModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Owns Phase 4 staff review, document decisions, corrections, and screening. */
class AdmissionReviewService
{
    private const APPLICATION_REVIEW_STATUSES = ['in_review', 'correction_requested', 'reviewed', 'screening_pending'];
    private const DOCUMENT_DECISIONS = ['approved', 'rejected', 'needs_correction'];
    private const SCREENING_OUTCOMES = ['pending', 'passed', 'failed', 'absent', 'referred'];
    private const REVIEWABLE_APPLICATION_STATUSES = ['submitted', 'under_review', 'correction_requested', 'reviewed', 'screening_pending', 'screened'];

    /** @return array<string, mixed> */
    public function queue(array $filters = []): array
    {
        $this->assertAuthority('admissions.applications.view');
        $status = trim((string) ($filters['status'] ?? ''));
        $model = (new ApplicantApplicationModel())->whereIn('status', self::REVIEWABLE_APPLICATION_STATUSES);
        if ($status !== '' && in_array($status, self::REVIEWABLE_APPLICATION_STATUSES, true)) {
            $model->where('status', $status);
        }

        return [
            'applications' => $model->orderBy('submitted_at', 'ASC')->findAll(100),
            'statusFilter' => $status,
            'metrics' => $this->metrics(),
        ];
    }

    /** @return array<string, mixed> */
    public function detail(int $applicationId): array
    {
        $this->assertAuthority('admissions.applications.view');
        $application = $this->application($applicationId);
        $sittings = (new ApplicationOlevelSittingModel())->where('applicant_application_id', $applicationId)->findAll();
        $resultModel = new ApplicationOlevelResultModel();
        foreach ($sittings as &$sitting) {
            $sitting['results'] = $resultModel->where('olevel_sitting_id', $sitting['id'])->findAll();
        }

        return [
            'application' => $application,
            'profile' => (new ApplicantProfileModel())->find((int) $application['applicant_profile_id']),
            'biodata' => (new ApplicationBiodataDraftModel())->where('applicant_application_id', $applicationId)->first(),
            'documents' => (new ApplicationDocumentModel())->where('application_id', $applicationId)->orderBy('document_type', 'ASC')->findAll(),
            'olevelSittings' => $sittings,
            'reviews' => (new AdmissionApplicationReviewModel())->where('applicant_application_id', $applicationId)->orderBy('reviewed_at', 'DESC')->findAll(),
            'screenings' => (new AdmissionScreeningRecordModel())->where('applicant_application_id', $applicationId)->orderBy('created_at', 'DESC')->findAll(),
            'auditEvents' => $this->can('admissions.audit.view') ? $this->auditEvents('applicant_application', $applicationId) : [],
        ];
    }

    public function reviewApplication(int $applicationId, array $payload): int
    {
        $this->assertAuthority('admissions.applications.review');
        $application = $this->application($applicationId);
        $reviewStatus = (string) ($payload['review_status'] ?? 'in_review');
        if (! in_array($reviewStatus, self::APPLICATION_REVIEW_STATUSES, true)) {
            throw new InvalidArgumentException('Unsupported application review status.');
        }

        $now = Time::now();
        $correctionUntil = null;
        if ($reviewStatus === 'correction_requested') {
            $hours = max(1, min(336, (int) ($payload['correction_hours'] ?? 72)));
            $correctionUntil = $now->addHours($hours)->toDateTimeString();
            if (trim((string) ($payload['public_correction_message'] ?? '')) === '') {
                throw new InvalidArgumentException('Correction requests require an applicant-facing message.');
            }
        }

        $id = (int) (new AdmissionApplicationReviewModel())->insert([
            'applicant_application_id' => $applicationId,
            'review_status' => $reviewStatus,
            'reviewer_user_id' => (new IdentityGuard())->userId(),
            'private_notes' => trim((string) ($payload['private_notes'] ?? '')) ?: null,
            'public_correction_message' => trim((string) ($payload['public_correction_message'] ?? '')) ?: null,
            'correction_allowed_until' => $correctionUntil,
            'reviewed_at' => $now->toDateTimeString(),
        ], true);

        (new ApplicantApplicationModel())->update($applicationId, ['status' => $this->applicationStatusForReview($reviewStatus), 'last_saved_at' => $now->toDateTimeString()]);
        service('auditLogger')->record('admissions.application.reviewed', ['target_type' => 'applicant_application', 'target_id' => $application['id'], 'summary' => 'Admissions staff updated application review status.', 'metadata' => ['review_status' => $reviewStatus, 'correction_allowed_until' => $correctionUntil]]);

        return $id;
    }

    public function reviewDocument(int $documentId, array $payload): int
    {
        $this->assertAuthority('admissions.documents.review');
        $document = (new ApplicationDocumentModel())->find($documentId);
        if ($document === null || empty($document['application_id'])) {
            throw new InvalidArgumentException('Applicant document was not found for this tenant.');
        }
        $decision = (string) ($payload['decision'] ?? '');
        if (! in_array($decision, self::DOCUMENT_DECISIONS, true)) {
            throw new InvalidArgumentException('Unsupported document review decision.');
        }

        $now = Time::now()->toDateTimeString();
        (new ApplicationDocumentModel())->update($documentId, ['review_status' => $decision]);
        $id = (int) (new AdmissionDocumentReviewLogModel())->insert([
            'applicant_application_id' => $document['application_id'],
            'application_document_id' => $documentId,
            'decision' => $decision,
            'reviewer_user_id' => (new IdentityGuard())->userId(),
            'private_notes' => trim((string) ($payload['private_notes'] ?? '')) ?: null,
            'applicant_message' => trim((string) ($payload['applicant_message'] ?? '')) ?: null,
            'reviewed_at' => $now,
        ], true);
        service('auditLogger')->record('admissions.document.reviewed', ['target_type' => 'application_document', 'target_id' => $documentId, 'summary' => 'Admissions staff reviewed an applicant document.', 'metadata' => ['decision' => $decision]]);

        return $id;
    }

    public function recordScreening(int $applicationId, array $payload): int
    {
        $this->assertAuthority('admissions.screening.manage');
        $application = $this->application($applicationId);
        $outcome = (string) ($payload['outcome'] ?? 'pending');
        if (! in_array($outcome, self::SCREENING_OUTCOMES, true)) {
            throw new InvalidArgumentException('Unsupported screening outcome.');
        }

        $decidedAt = $outcome === 'pending' ? null : Time::now()->toDateTimeString();
        $id = (int) (new AdmissionScreeningRecordModel())->insert([
            'applicant_application_id' => $applicationId,
            'screening_type' => trim((string) ($payload['screening_type'] ?? 'manual_review')) ?: 'manual_review',
            'scheduled_at' => trim((string) ($payload['scheduled_at'] ?? '')) ?: null,
            'held_at' => trim((string) ($payload['held_at'] ?? '')) ?: null,
            'venue' => trim((string) ($payload['venue'] ?? '')) ?: null,
            'score' => ($payload['score'] ?? '') === '' ? null : (float) $payload['score'],
            'outcome' => $outcome,
            'private_notes' => trim((string) ($payload['private_notes'] ?? '')) ?: null,
            'decided_at' => $decidedAt,
        ], true);
        (new ApplicantApplicationModel())->update($applicationId, ['status' => $outcome === 'pending' ? 'screening_pending' : 'screened']);
        service('auditLogger')->record('admissions.screening.recorded', ['target_type' => 'applicant_application', 'target_id' => $application['id'], 'summary' => 'Admissions staff recorded screening outcome.', 'metadata' => ['outcome' => $outcome]]);

        return $id;
    }

    /** @return array<string, int> */
    public function metrics(): array
    {
        $this->assertAuthority('admissions.applications.view');
        $model = new ApplicantApplicationModel();

        return [
            'submitted' => (clone $model)->where('status', 'submitted')->countAllResults(),
            'under_review' => (new ApplicantApplicationModel())->where('status', 'under_review')->countAllResults(),
            'correction_requested' => (new ApplicantApplicationModel())->where('status', 'correction_requested')->countAllResults(),
            'screening_pending' => (new ApplicantApplicationModel())->where('status', 'screening_pending')->countAllResults(),
            'screened' => (new ApplicantApplicationModel())->where('status', 'screened')->countAllResults(),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function auditEvents(string $targetType = 'applicant_application', ?int $targetId = null): array
    {
        $this->assertAuthority('admissions.audit.view');
        $context = service('tenantContextManager')->current();
        $query = (new AuditLogModel())->where('tenant_id', $context->tenantId)->like('action', 'admissions.', 'after');
        if ($targetId !== null) {
            $query->where('target_type', $targetType)->where('target_id', (string) $targetId);
        }

        return $query->orderBy('created_at', 'DESC')->findAll(100);
    }

    /** @return array<string, mixed> */
    private function application(int $applicationId): array
    {
        $application = (new ApplicantApplicationModel())->find($applicationId);
        if ($application === null || ! in_array($application['status'], self::REVIEWABLE_APPLICATION_STATUSES, true)) {
            throw new InvalidArgumentException('Submitted application was not found for this tenant.');
        }

        return $application;
    }

    private function assertAuthority(string $authority): void
    {
        if (! $this->can($authority)) {
            throw new InvalidArgumentException('You do not have authority to access this admissions workspace.');
        }
    }

    private function can(string $authority): bool
    {
        $context = service('tenantContextManager')->current();

        return service('tenantAccess')->isMember($context) && service('tenantAccess')->hasAuthority($context, $authority);
    }

    private function applicationStatusForReview(string $reviewStatus): string
    {
        return match ($reviewStatus) {
            'correction_requested' => 'correction_requested',
            'reviewed' => 'reviewed',
            'screening_pending' => 'screening_pending',
            default => 'under_review',
        };
    }
}
