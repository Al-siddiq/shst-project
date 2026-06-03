<?php

namespace App\Services\Admissions;

/** Stable Block 3 operational-authority catalogue. */
class AdmissionAuthorityCatalog
{
    /** @var array<string, string> */
    public const AUTHORITIES = [
        'admissions.dashboard.view' => 'View admissions dashboard',
        'admissions.cycles.manage' => 'Manage admission cycles',
        'admissions.programmes.manage' => 'Manage admission programme openings',
        'admissions.requirements.manage' => 'Manage admission requirements',
        'admissions.applications.view' => 'View applications',
        'admissions.applications.review' => 'Review applications',
        'admissions.documents.review' => 'Review applicant documents',
        'admissions.screening.manage' => 'Manage screening',
        'admissions.shortlist.manage' => 'Manage shortlisting',
        'admissions.decisions.manage' => 'Manage admission decisions',
        'admissions.decisions.approve' => 'Approve admission decisions',
        'admissions.lists.preview' => 'Preview admission lists',
        'admissions.lists.publish' => 'Publish admission lists',
        'admissions.acceptance.view' => 'View admission acceptances',
        'admissions.clearance.manage' => 'Manage admission clearance status',
        'admissions.reports.view' => 'View admission reports',
        'admissions.audit.view' => 'View admissions audit trail',
    ];
}
