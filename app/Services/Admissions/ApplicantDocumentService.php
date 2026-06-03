<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionDocumentRequirementModel;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Owns private applicant document uploads, replacement, and retry metadata. */
class ApplicantDocumentService
{
    /** @return array<string, mixed> */
    public function upload(string $applicationToken, string $documentType, UploadedFile $file): array
    {
        $application = service('applicantAccessPolicy')->ownedApplication(service('tenantContextManager')->current(), $applicationToken);
        if ($application === null) {
            throw new InvalidArgumentException('Application was not found for this applicant.');
        }
        service('admissionCorrectionWindow')->assertApplicantMayEdit($application);

        $documentType = trim($documentType);
        $requirement = $this->requirementFor($application, $documentType);
        if ($requirement === null) {
            throw new InvalidArgumentException('Select a configured document requirement for this programme.');
        }

        $stored = service('applicantDocumentStorage')->store($file, $application, $documentType, $requirement);
        $model = new ApplicationDocumentModel();
        $payload = array_merge($stored, [
            'applicant_profile_id' => $application['applicant_profile_id'],
            'application_id' => $application['id'],
            'public_token' => bin2hex(random_bytes(16)),
            'document_type' => $documentType,
            'review_status' => 'pending',
        ]);
        $existing = $model->where('application_id', $application['id'])->where('document_type', $documentType)->first();
        $documentId = $existing === null ? (int) $model->insert($payload, true) : (int) $existing['id'];
        if ($existing !== null) {
            $model->update($documentId, $payload);
            // Replacement keeps one active metadata row per configured document
            // type while removing the old private blob when it is still present.
            try {
                $oldPath = service('applicantDocumentStorage')->privateFile($existing);
                @unlink($oldPath);
            } catch (\Throwable) {
                // Missing old files should not invalidate an otherwise valid retry.
            }
        }

        (new \App\Models\Tenant\Admissions\ApplicantApplicationModel())->update((int) $application['id'], ['last_saved_at' => Time::now()->toDateTimeString()]);
        service('auditLogger')->record('admissions.application.document_uploaded', ['target_type' => 'application_document', 'target_id' => $documentId, 'summary' => 'Applicant uploaded or replaced a private admission document.', 'metadata' => ['document_type' => $documentType]]);

        return $model->find($documentId);
    }

    /** @return list<array<string, mixed>> */
    public function documentsForApplication(int $applicationId): array
    {
        return (new ApplicationDocumentModel())->where('application_id', $applicationId)->orderBy('document_type', 'ASC')->findAll();
    }

    /** @param array<string, mixed> $application @return array<string, mixed>|null */
    public function requirementFor(array $application, string $documentType): ?array
    {
        return (new AdmissionDocumentRequirementModel())
            ->where('admission_cycle_id', $application['admission_cycle_id'])
            ->where('programme_opening_id', $application['programme_opening_id'])
            ->where('document_type', $documentType)
            ->where('status', 'active')
            ->first();
    }
}
