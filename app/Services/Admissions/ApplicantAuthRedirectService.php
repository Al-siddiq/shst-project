<?php

namespace App\Services\Admissions;

/** Keeps tenant-aware Shield redirect targets centralized for Phase 2. */
class ApplicantAuthRedirectService
{
    /** @return array<string, string> */
    public function links(): array
    {
        return [
            'login' => site_url('auth/login?return=' . rawurlencode(current_url())),
            'register' => site_url('auth/register?return=' . rawurlencode(current_url())),
            'dashboard' => site_url('applicant'),
        ];
    }
}
