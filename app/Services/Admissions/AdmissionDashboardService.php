<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionCycleModel;
use App\Models\Tenant\Admissions\AdmissionDocumentRequirementModel;
use App\Models\Tenant\Admissions\AdmissionProgrammeOpeningModel;
use App\Models\Tenant\Admissions\AdmissionRequirementDefinitionModel;
use App\Models\Tenant\Admissions\AdmissionSubjectRequirementModel;

/** Builds Phase 1 admission setup metrics; application counts arrive later. */
class AdmissionDashboardService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context) || ! service('tenantAccess')->hasAuthority($context, 'admissions.dashboard.view')) {
            throw new \InvalidArgumentException('You do not have authority to view the admissions dashboard.');
        }
        $cycleModel = new AdmissionCycleModel();
        $activeCycle = service('publicAdmissions')->activeCycle();
        $openProgrammes = $activeCycle === null ? [] : service('publicAdmissions')->openProgrammes((int) $activeCycle['id']);
        $gaps = [];
        if ($cycleModel->countAllResults() === 0) {
            $gaps[] = 'Create an admission cycle.';
        }
        if ($activeCycle === null) {
            $gaps[] = 'Open and publish an admission cycle.';
        }
        if ($openProgrammes === []) {
            $gaps[] = 'Open at least one active programme.';
        }
        if ((new AdmissionRequirementDefinitionModel())->where('status', 'active')->countAllResults() === 0 && (new AdmissionSubjectRequirementModel())->where('status', 'active')->countAllResults() === 0 && (new AdmissionDocumentRequirementModel())->where('status', 'active')->countAllResults() === 0) {
            $gaps[] = 'Configure admission requirements.';
        }

        return [
            'activeCycle' => $activeCycle,
            'cycleCount' => (new AdmissionCycleModel())->countAllResults(),
            'openProgrammeCount' => count($openProgrammes),
            'requirementCount' => (new AdmissionRequirementDefinitionModel())->where('status', 'active')->countAllResults(),
            'subjectRequirementCount' => (new AdmissionSubjectRequirementModel())->where('status', 'active')->countAllResults(),
            'documentRequirementCount' => (new AdmissionDocumentRequirementModel())->where('status', 'active')->countAllResults(),
            'setupGaps' => $gaps,
        ];
    }
}
