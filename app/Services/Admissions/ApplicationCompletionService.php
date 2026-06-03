<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionCycleModel;
use App\Models\Tenant\Admissions\AdmissionDocumentRequirementModel;
use App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel;
use App\Models\Tenant\Admissions\ApplicationBiodataDraftModel;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use App\Models\Tenant\Admissions\ApplicationOlevelResultModel;
use App\Models\Tenant\Admissions\ApplicationOlevelSittingModel;
use CodeIgniter\I18n\Time;

/** Evaluates applicant readiness without trusting browser-side completion state. */
class ApplicationCompletionService
{
    /** @param array<string, mixed> $application @return array<string, mixed> */
    public function evaluate(array $application): array
    {
        $missing = [];
        $warnings = [];

        $opening = (new AdmissionProgrammeOpeningModel())->find((int) $application['programme_opening_id']);
        $cycle = (new AdmissionCycleModel())->find((int) $application['admission_cycle_id']);
        if ($opening === null || $cycle === null || $opening['status'] !== 'open' || $cycle['status'] !== 'open' || ! (bool) $cycle['is_public']) {
            $missing[] = 'This admission programme is no longer open.';
        } elseif (! $this->cycleIsCurrentlyOpen($cycle)) {
            $missing[] = 'The admission cycle is outside its opening dates.';
        }

        $biodata = (new ApplicationBiodataDraftModel())->where('applicant_application_id', $application['id'])->first();
        foreach (['surname', 'first_name', 'gender', 'date_of_birth', 'phone_e164', 'email', 'residential_address', 'state_of_origin', 'lga_of_origin'] as $field) {
            if ($biodata === null || empty($biodata[$field])) {
                $missing[] = 'Biodata: ' . str_replace('_', ' ', $field) . ' is required.';
            }
        }

        $sittings = (new ApplicationOlevelSittingModel())->where('applicant_application_id', $application['id'])->findAll();
        $results = (new ApplicationOlevelResultModel())->where('applicant_application_id', $application['id'])->findAll();
        if ($sittings === [] || $results === []) {
            $missing[] = 'At least one O\'Level sitting and subject result is required.';
        }

        $requiredDocs = (new AdmissionDocumentRequirementModel())
            ->where('admission_cycle_id', $application['admission_cycle_id'])
            ->where('programme_opening_id', $application['programme_opening_id'])
            ->where('is_required', 1)
            ->where('status', 'active')
            ->findAll();
        $uploaded = (new ApplicationDocumentModel())->where('application_id', $application['id'])->findAll();
        $uploadedTypes = array_column($uploaded, 'document_type');
        foreach ($requiredDocs as $requirement) {
            if (! in_array($requirement['document_type'], $uploadedTypes, true)) {
                $missing[] = 'Document: ' . $requirement['label'] . ' is required.';
            }
        }
        if ($requiredDocs === []) {
            $warnings[] = 'No required document has been configured for this programme.';
        }

        return [
            'complete' => $missing === [],
            'missing' => $missing,
            'warnings' => $warnings,
            'counts' => ['sittings' => count($sittings), 'results' => count($results), 'documents' => count($uploaded), 'required_documents' => count($requiredDocs)],
            'biodata' => $biodata,
            'olevel' => ['sittings' => $sittings, 'results' => $results],
            'documents' => $uploaded,
        ];
    }

    /** @param array<string, mixed> $cycle */
    private function cycleIsCurrentlyOpen(array $cycle): bool
    {
        $now = Time::now()->getTimestamp();
        if (! empty($cycle['opens_at']) && strtotime((string) $cycle['opens_at']) > $now) {
            return false;
        }
        if (! empty($cycle['closes_at']) && strtotime((string) $cycle['closes_at']) < $now) {
            return false;
        }

        return true;
    }
}
