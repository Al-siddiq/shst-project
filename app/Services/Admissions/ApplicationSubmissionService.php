<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use App\Models\Tenant\Admissions\ApplicationSubmissionSnapshotModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Runs the Phase 3 idempotent final-submission transaction. */
class ApplicationSubmissionService
{
    /** @return array<string, mixed> */
    public function preview(string $token): array
    {
        $application = $this->ownedApplication($token);
        $completion = service('applicationCompletion')->evaluate($application);

        return array_merge(['application' => $application], $completion);
    }

    /** @return array<string, mixed> */
    public function submit(string $token): array
    {
        $application = $this->ownedApplication($token);
        if ($application['status'] === 'submitted') {
            return $this->submittedResponse($application, true);
        }
        if ($application['status'] !== 'draft') {
            throw new InvalidArgumentException('Only draft applications can be submitted.');
        }

        $db = db_connect();
        $db->transStart();
        $application = (new ApplicantApplicationModel())->find((int) $application['id']);
        if ($application['status'] === 'submitted') {
            $db->transComplete();
            return $this->submittedResponse($application, true);
        }

        $completion = service('applicationCompletion')->evaluate($application);
        if (! $completion['complete']) {
            $db->transComplete();
            throw new InvalidArgumentException('Application is incomplete: ' . implode(' ', $completion['missing']));
        }

        $applicationNumber = service('admissionReferenceGenerator')->next('application');
        $submittedAt = Time::now()->toDateTimeString();
        $snapshot = [
            'application' => $application,
            'biodata' => $completion['biodata'],
            'olevel' => $completion['olevel'],
            'documents' => array_map(static fn (array $document): array => array_diff_key($document, ['storage_path' => true]), $completion['documents']),
        ];
        $snapshotId = (int) (new ApplicationSubmissionSnapshotModel())->insert([
            'applicant_application_id' => $application['id'],
            'application_number' => $applicationNumber,
            'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_SLASHES),
            'submitted_at' => $submittedAt,
        ], true);
        (new ApplicantApplicationModel())->update((int) $application['id'], [
            'application_number' => $applicationNumber,
            'status' => 'submitted',
            'submitted_at' => $submittedAt,
            'submission_snapshot_id' => $snapshotId,
            'last_saved_at' => $submittedAt,
        ]);
        service('admissionNotificationDispatcher')->queue('admissions.application.submitted', $completion['biodata']['email'] ?? null, ['application_number' => $applicationNumber, 'application_id' => $application['id']]);
        service('auditLogger')->record('admissions.application.submitted', ['target_type' => 'applicant_application', 'target_id' => $application['id'], 'summary' => 'Applicant submitted an admission application.', 'metadata' => ['application_number' => $applicationNumber]]);
        $db->transComplete();

        return $this->submittedResponse((new ApplicantApplicationModel())->find((int) $application['id']), false);
    }

    /** @return array<string, mixed> */
    private function ownedApplication(string $token): array
    {
        $application = service('applicantAccessPolicy')->ownedApplication(service('tenantContextManager')->current(), $token);
        if ($application === null) {
            throw new InvalidArgumentException('Application was not found for this applicant.');
        }

        return $application;
    }

    /** @param array<string, mixed> $application @return array<string, mixed> */
    private function submittedResponse(array $application, bool $idempotentRetry): array
    {
        return ['application' => $application, 'application_number' => $application['application_number'], 'idempotent_retry' => $idempotentRetry];
    }
}
