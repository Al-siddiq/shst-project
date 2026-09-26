<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use App\Models\Tenant\Admissions\ApplicationSubmissionSnapshotModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

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
        if (! in_array($application['status'], ['draft', 'correction_requested'], true)) {
            throw new InvalidArgumentException('Only editable applications can be submitted.');
        }

        return service('transactional')->run(function ($db) use ($application): array {
            $table = $db->prefixTable('applicant_applications');
            $suffix = $db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE';
            $application = $db->query("SELECT * FROM {$table} WHERE id = ? AND tenant_id = ?{$suffix}", [$application['id'], $application['tenant_id']])->getRowArray();
            if ($application === null) {
                throw new InvalidArgumentException('Application was not found for this applicant.');
            }
            if ($application['status'] === 'submitted') {
                return $this->submittedResponse($application, true);
            }
            if (! in_array($application['status'], ['draft', 'correction_requested'], true)) {
                throw new InvalidArgumentException('Only editable applications can be submitted.');
            }

            $completion = service('applicationCompletion')->evaluate($application);
            if (! $completion['complete']) {
                throw new InvalidArgumentException('Application is incomplete: ' . implode(' ', $completion['missing']));
            }
            $version = ((int) ($application['submission_version'] ?? 0)) + 1;
            $applicationNumber = $application['application_number'] ?: service('admissionReferenceGenerator')->next('application');
            $submittedAt = Time::now()->toDateTimeString();
            $snapshot = ['version' => $version, 'application' => $application, 'biodata' => $completion['biodata'], 'olevel' => $completion['olevel'], 'documents' => array_map(static fn (array $document): array => array_diff_key($document, ['storage_path' => true]), $completion['documents'])];
            $snapshotId = (new ApplicationSubmissionSnapshotModel())->insert([
                'applicant_application_id' => $application['id'], 'application_number' => $applicationNumber,
                'submission_version' => $version, 'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_SLASHES), 'submitted_at' => $submittedAt,
            ], true);
            if (! is_int($snapshotId) && ! ctype_digit((string) $snapshotId)) {
                throw new RuntimeException('Immutable application snapshot could not be recorded.');
            }
            $updated = (new ApplicantApplicationModel())->where('lock_version', (int) ($application['lock_version'] ?? 0))->update((int) $application['id'], [
                'application_number' => $applicationNumber, 'status' => 'submitted', 'submitted_at' => $submittedAt,
                'submission_snapshot_id' => $snapshotId, 'submission_version' => $version,
                'lock_version' => ((int) ($application['lock_version'] ?? 0)) + 1, 'last_saved_at' => $submittedAt,
            ]);
            if (! $updated) {
                throw new RuntimeException('Application changed during submission; retry safely.');
            }
            $payload = ['application_number' => $applicationNumber, 'application_id' => $application['id'], 'submission_version' => $version];
            service('admissionNotificationDispatcher')->queueApplicant('admissions.application.submitted', (int)$application['id'], $payload);
            service('auditLogger')->record('admissions.application.submitted', ['target_type' => 'applicant_application', 'target_id' => $application['id'], 'summary' => 'Applicant submitted an immutable application version.', 'metadata' => $payload]);

            return $this->submittedResponse((new ApplicantApplicationModel())->find((int) $application['id']), false);
        });
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
