<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;

/** Minimal Phase 0 endpoint proving the protected applicant route boundary. */
class DashboardController extends BaseController
{
    use ApiResponseTrait;

    public function readiness()
    {
        $profile = service('applicantAccessPolicy')->currentProfile(service('tenantContextManager')->current());

        return $this->ok('Applicant identity and tenant context are ready.', [
            'applicant_profile_id' => $profile['id'],
            'status' => $profile['status'],
        ]);
    }
}
