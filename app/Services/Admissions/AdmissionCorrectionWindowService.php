<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionApplicationReviewModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Validates temporary applicant correction access granted by admissions staff. */
class AdmissionCorrectionWindowService
{
    /** @param array<string, mixed> $application */
    public function assertApplicantMayEdit(array $application): void
    {
        if (($application['status'] ?? '') === 'draft') {
            return;
        }
        if (! $this->isOpen($application)) {
            throw new InvalidArgumentException('This application is no longer editable. A correction window must be explicitly open.');
        }
    }

    /** @param array<string, mixed> $application */
    public function isOpen(array $application): bool
    {
        if (($application['status'] ?? '') !== 'correction_requested') {
            return false;
        }
        $review = (new AdmissionApplicationReviewModel())
            ->where('applicant_application_id', $application['id'])
            ->where('review_status', 'correction_requested')
            ->orderBy('reviewed_at', 'DESC')
            ->first();

        return $review !== null
            && ! empty($review['correction_allowed_until'])
            && strtotime((string) $review['correction_allowed_until']) >= Time::now()->getTimestamp();
    }
}
