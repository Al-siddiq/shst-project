<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;

/** Mobile-first applicant dashboard for owned tenant draft applications. */
class DashboardController extends BaseController
{
    use ApiResponseTrait;

    public function index(): string
    {
        $profile = service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current());
        $data = service('publicWebsite')->page('apply', 'Applicant dashboard');

        return view('applicant/dashboard', array_merge($data, [
            'profile' => $profile,
            'drafts' => service('applicationDraft')->draftsForCurrentApplicant(),
            'openProgrammes' => service('publicAdmissions')->openProgrammes(),
            'activeOffer' => service('admissionDecision')->currentOfferForApplicant(),
            'publishedListEntries' => service('admissionListPublication')->applicantPublishedEntries(),
        ]));
    }

    public function readiness()
    {
        $profile = service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current());

        return $this->ok('Applicant identity and tenant context are ready.', [
            'applicant_profile_id' => $profile['id'],
            'status' => $profile['status'],
        ]);
    }
}
