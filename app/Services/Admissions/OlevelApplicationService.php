<?php

namespace App\Services\Admissions;

use App\Models\Admissions\OlevelExamTypeModel;
use App\Models\Admissions\OlevelGradeModel;
use App\Models\Admissions\OlevelSubjectModel;
use App\Models\Tenant\Admissions\ApplicationOlevelResultModel;
use App\Models\Tenant\Admissions\ApplicationOlevelSittingModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Owns applicant O'Level sittings and result rows before final submission. */
class OlevelApplicationService
{
    /** @return array<string, mixed> */
    public function references(): array
    {
        return [
            'exam_types' => (new OlevelExamTypeModel())->where('status', 'active')->orderBy('sort_order', 'ASC')->findAll(),
            'subjects' => (new OlevelSubjectModel())->where('status', 'active')->orderBy('sort_order', 'ASC')->findAll(),
            'grades' => (new OlevelGradeModel())->where('status', 'active')->orderBy('sort_order', 'ASC')->findAll(),
        ];
    }

    /** @return array<string, mixed> */
    public function save(string $applicationToken, array $payload): array
    {
        $application = service('applicantAccessPolicy')->ownedApplication(service('tenantContextManager')->current(), $applicationToken);
        if ($application === null) {
            throw new InvalidArgumentException('Application was not found for this applicant.');
        }
        if ($application['status'] !== 'draft') {
            throw new InvalidArgumentException('Submitted applications cannot be edited.');
        }

        $examType = strtoupper(trim((string) ($payload['exam_type_code'] ?? '')));
        $year = (int) ($payload['exam_year'] ?? 0);
        if (! $this->examTypeExists($examType) || $year < 1970 || $year > ((int) date('Y') + 1)) {
            throw new InvalidArgumentException('Provide a valid exam type and year.');
        }

        $sittingModel = new ApplicationOlevelSittingModel();
        $sittingId = ! empty($payload['sitting_id']) ? (int) $payload['sitting_id'] : null;
        $sitting = $sittingId !== null ? $sittingModel->where('id', $sittingId)->where('applicant_application_id', $application['id'])->first() : null;
        $sittingData = [
            'applicant_application_id' => $application['id'],
            'exam_type_code' => $examType,
            'exam_year' => $year,
            'exam_number' => strtoupper(trim((string) ($payload['exam_number'] ?? ''))) ?: null,
            'sitting_label' => trim((string) ($payload['sitting_label'] ?? '')) ?: $examType . ' ' . $year,
        ];
        if ($sitting === null) {
            $sittingId = (int) $sittingModel->insert($sittingData, true);
        } else {
            $sittingModel->update((int) $sitting['id'], $sittingData);
            $sittingId = (int) $sitting['id'];
        }

        $resultModel = new ApplicationOlevelResultModel();
        foreach ($this->normalizeResults($payload['results'] ?? []) as $result) {
            $result['applicant_application_id'] = $application['id'];
            $result['olevel_sitting_id'] = $sittingId;
            $existing = $resultModel->where('olevel_sitting_id', $sittingId)->where('subject_code', $result['subject_code'])->first();
            $existing === null ? $resultModel->insert($result) : $resultModel->update((int) $existing['id'], $result);
        }

        service('auditLogger')->record('admissions.application.olevel_saved', ['target_type' => 'applicant_application', 'target_id' => $application['id'], 'summary' => 'Applicant saved O\'Level results.', 'metadata' => ['sitting_id' => $sittingId]]);
        (new \App\Models\Tenant\Admissions\ApplicantApplicationModel())->update((int) $application['id'], ['last_saved_at' => Time::now()->toDateTimeString()]);

        return $this->forApplication((int) $application['id']);
    }

    /** @return array<string, mixed> */
    public function forApplication(int $applicationId): array
    {
        $sittings = (new ApplicationOlevelSittingModel())->where('applicant_application_id', $applicationId)->orderBy('id', 'ASC')->findAll();
        $resultModel = new ApplicationOlevelResultModel();
        foreach ($sittings as &$sitting) {
            $sitting['results'] = $resultModel->where('olevel_sitting_id', $sitting['id'])->orderBy('subject_name', 'ASC')->findAll();
        }

        return ['sittings' => $sittings, 'references' => $this->references()];
    }

    private function examTypeExists(string $code): bool
    {
        return $code !== '' && (new OlevelExamTypeModel())->where('code', $code)->where('status', 'active')->first() !== null;
    }

    /** @return list<array<string, mixed>> */
    private function normalizeResults(array $rows): array
    {
        $normalized = [];
        foreach ($rows as $row) {
            $subjectCode = strtoupper(trim((string) ($row['subject_code'] ?? '')));
            $gradeCode = strtoupper(trim((string) ($row['grade_code'] ?? '')));
            $subject = $subjectCode !== '' ? (new OlevelSubjectModel())->where('code', $subjectCode)->where('status', 'active')->first() : null;
            $grade = $gradeCode !== '' ? (new OlevelGradeModel())->where('code', $gradeCode)->where('status', 'active')->first() : null;
            if ($subject === null || $grade === null) {
                throw new InvalidArgumentException('Each O\'Level row must use a valid subject and grade.');
            }
            $normalized[] = ['subject_code' => $subjectCode, 'subject_name' => $subject['label'], 'grade_code' => $gradeCode];
        }
        if ($normalized === []) {
            throw new InvalidArgumentException('Add at least one O\'Level subject result.');
        }

        return $normalized;
    }
}
