<?php

namespace App\Services\Admissions;

use App\Entities\TenantContext;
use App\Libraries\Auth\IdentityGuard;
use App\Models\Tenant\Admissions\ApplicantProfileModel;
use App\Models\Tenant\Admissions\ApplicationDocumentModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;

/**
 * Applicant authorization skeleton.
 *
 * Tenant scope is necessary but insufficient: applicant-facing reads and
 * writes must also prove ownership by the authenticated Shield identity.
 */
class ApplicantAccessPolicy
{
    public function __construct(private readonly IdentityGuard $guard = new IdentityGuard())
    {
    }

    /** @return array<string, mixed>|null */
    public function currentProfile(TenantContext $context): ?array
    {
        $userId = $this->guard->userId();
        if (! $this->guard->check() || ! $context->isResolved() || $userId === null) {
            return null;
        }

        return (new ApplicantProfileModel())
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }


    /** @return array<string, mixed>|null */
    public function ownedApplication(TenantContext $context, string $token): ?array
    {
        $profile = $this->currentProfile($context);
        if ($profile === null) {
            return null;
        }

        return (new ApplicantApplicationModel())
            ->where('public_token', $token)
            ->where('applicant_profile_id', $profile['id'])
            ->first();
    }

    /** @return array<string, mixed>|null */
    public function ownedDocument(TenantContext $context, string $token): ?array
    {
        $profile = $this->currentProfile($context);
        if ($profile === null) {
            return null;
        }

        return (new ApplicationDocumentModel())
            ->where('public_token', $token)
            ->where('applicant_profile_id', $profile['id'])
            ->first();
    }
}
