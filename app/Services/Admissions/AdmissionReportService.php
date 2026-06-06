<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionClearanceStatusModel;
use App\Models\Tenant\Admissions\AdmissionConversionEligibilityMarkerModel;
use App\Models\Tenant\Admissions\AdmissionDecisionModel;
use App\Models\Tenant\Admissions\AdmissionListEntryModel;
use App\Models\Tenant\Admissions\AdmissionListPublicationModel;
use App\Models\Tenant\Admissions\AdmissionOfferAcceptanceModel;
use App\Models\Tenant\Admissions\AdmissionOfferModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use InvalidArgumentException;

/**
 * Phase 8 reporting facade for Block 3 admissions.
 *
 * Reports deliberately expose operational counts and safe application metadata
 * only. Private reviewer notes, private documents, payment internals, and
 * student-conversion side effects remain outside this Block 3 reporting layer.
 */
class AdmissionReportService
{
    /** @var array<string, string> */
    private const REPORT_TITLES = [
        'pipeline' => 'Application pipeline',
        'decisions' => 'Admission decisions and offers',
        'acceptance' => 'Acceptance and clearance',
        'publications' => 'Admission list publications',
    ];

    /** @return array<string, mixed> */
    public function dashboard(): array
    {
        $this->assertAuthority();

        return [
            'reports' => self::REPORT_TITLES,
            'pipeline' => $this->pipelineCounts(),
            'decisions' => $this->decisionCounts(),
            'acceptance' => $this->acceptanceCounts(),
            'publications' => $this->publicationCounts(),
        ];
    }

    /** @return array{filename:string,content:string,mime:string} */
    public function csvExport(string $report): array
    {
        $this->assertAuthority();
        if (! array_key_exists($report, self::REPORT_TITLES)) {
            throw new InvalidArgumentException('Unsupported admissions report export.');
        }

        $rows = match ($report) {
            'pipeline' => $this->pipelineRows(),
            'decisions' => $this->decisionRows(),
            'acceptance' => $this->acceptanceRows(),
            'publications' => $this->publicationRows(),
        };

        return [
            'filename' => 'admissions-' . $report . '-' . date('Ymd-His') . '.csv',
            'content' => $this->toCsv($rows),
            'mime' => 'text/csv; charset=UTF-8',
        ];
    }

    /** @return array<string, int> */
    public function pipelineCounts(): array
    {
        $statuses = ['draft', 'submitted', 'under_review', 'correction_requested', 'reviewed', 'screening_pending', 'screened', 'shortlisted', 'waitlisted', 'rejected', 'offered', 'accepted', 'declined', 'eligible_for_student_conversion'];

        return $this->countByStatuses(new ApplicantApplicationModel(), 'status', $statuses);
    }

    /** @return array<string, int> */
    public function decisionCounts(): array
    {
        return [
            'current_offered_decisions' => (new AdmissionDecisionModel())->where('is_current', 1)->where('decision_type', 'offer')->countAllResults(),
            'current_waitlisted_decisions' => (new AdmissionDecisionModel())->where('is_current', 1)->where('decision_type', 'waitlist')->countAllResults(),
            'current_rejected_decisions' => (new AdmissionDecisionModel())->where('is_current', 1)->where('decision_type', 'reject')->countAllResults(),
            'pending_approval' => (new AdmissionDecisionModel())->where('decision_status', 'pending_approval')->countAllResults(),
            'issued_offers' => (new AdmissionOfferModel())->where('offer_status', 'issued')->countAllResults(),
            'accepted_offers' => (new AdmissionOfferModel())->where('offer_status', 'accepted')->countAllResults(),
        ];
    }

    /** @return array<string, int> */
    public function acceptanceCounts(): array
    {
        return [
            'accepted' => (new AdmissionOfferAcceptanceModel())->where('acceptance_status', 'accepted')->countAllResults(),
            'declined' => (new AdmissionOfferAcceptanceModel())->where('acceptance_status', 'declined')->countAllResults(),
            'clearance_pending' => (new AdmissionClearanceStatusModel())->where('clearance_status', 'pending')->countAllResults(),
            'clearance_in_progress' => (new AdmissionClearanceStatusModel())->where('clearance_status', 'in_progress')->countAllResults(),
            'cleared' => (new AdmissionClearanceStatusModel())->where('clearance_status', 'cleared')->countAllResults(),
            'conversion_eligible' => (new AdmissionConversionEligibilityMarkerModel())->countAllResults(),
        ];
    }

    /** @return array<string, int> */
    public function publicationCounts(): array
    {
        return [
            'draft_lists' => (new AdmissionListPublicationModel())->where('status', 'draft')->countAllResults(),
            'published_lists' => (new AdmissionListPublicationModel())->where('status', 'published')->countAllResults(),
            'public_entries' => (new AdmissionListEntryModel())->where('entry_status', 'published')->countAllResults(),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function pipelineRows(): array
    {
        return array_map(static fn (array $row): array => [
            'application_number' => $row['application_number'] ?? '',
            'admission_cycle_id' => $row['admission_cycle_id'] ?? '',
            'programme_opening_id' => $row['programme_opening_id'] ?? '',
            'status' => $row['status'] ?? '',
            'submitted_at' => $row['submitted_at'] ?? '',
            'last_saved_at' => $row['last_saved_at'] ?? '',
        ], (new ApplicantApplicationModel())->orderBy('updated_at', 'DESC')->findAll(1000));
    }

    /** @return list<array<string, mixed>> */
    private function decisionRows(): array
    {
        return array_map(static fn (array $row): array => [
            'application_id' => $row['applicant_application_id'] ?? '',
            'decision_type' => $row['decision_type'] ?? '',
            'decision_status' => $row['decision_status'] ?? '',
            'requires_approval' => $row['requires_approval'] ?? '',
            'decided_at' => $row['decided_at'] ?? '',
            'approved_at' => $row['approved_at'] ?? '',
        ], (new AdmissionDecisionModel())->orderBy('updated_at', 'DESC')->findAll(1000));
    }

    /** @return list<array<string, mixed>> */
    private function acceptanceRows(): array
    {
        return array_map(static fn (array $row): array => [
            'application_id' => $row['applicant_application_id'] ?? '',
            'offer_id' => $row['admission_offer_id'] ?? '',
            'acceptance_status' => $row['acceptance_status'] ?? '',
            'acceptance_fee_status' => $row['acceptance_fee_status'] ?? '',
            'accepted_at' => $row['accepted_at'] ?? '',
            'declined_at' => $row['declined_at'] ?? '',
        ], (new AdmissionOfferAcceptanceModel())->orderBy('updated_at', 'DESC')->findAll(1000));
    }

    /** @return list<array<string, mixed>> */
    private function publicationRows(): array
    {
        return array_map(static fn (array $row): array => [
            'title' => $row['title'] ?? '',
            'version_number' => $row['version_number'] ?? '',
            'status' => $row['status'] ?? '',
            'published_at' => $row['published_at'] ?? '',
            'programme_opening_id' => $row['programme_opening_id'] ?? '',
            'admission_cycle_id' => $row['admission_cycle_id'] ?? '',
        ], (new AdmissionListPublicationModel())->orderBy('updated_at', 'DESC')->findAll(1000));
    }

    /** @param object $model @param list<string> $statuses @return array<string, int> */
    private function countByStatuses(object $model, string $field, array $statuses): array
    {
        $counts = [];
        foreach ($statuses as $status) {
            $counts[$status] = $model->where($field, $status)->countAllResults();
        }

        return $counts;
    }

    /** @param list<array<string,mixed>> $rows */
    private function toCsv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new InvalidArgumentException('Unable to prepare admissions CSV export.');
        }
        $headers = $rows === [] ? ['message'] : array_keys($rows[0]);
        fputcsv($stream, $headers);
        if ($rows === []) {
            fputcsv($stream, ['No records found for this tenant-scoped admissions report.']);
        }
        foreach ($rows as $row) {
            fputcsv($stream, array_map(static fn ($value): string => (string) $value, $row));
        }
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content === false ? '' : $content;
    }

    private function assertAuthority(): void
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context) || ! service('tenantAccess')->hasAuthority($context, 'admissions.reports.view')) {
            throw new InvalidArgumentException('You do not have authority to view admission reports.');
        }
    }
}
