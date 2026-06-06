<?php

namespace App\Services\Admissions;

use App\Libraries\Auth\IdentityGuard;
use App\Models\Tenant\Admissions\ApplicantProfileModel;
use InvalidArgumentException;

/** Creates or updates the tenant applicant profile owned by the Shield user. */
class ApplicantProfileService
{
    public function __construct(private readonly IdentityGuard $guard = new IdentityGuard())
    {
    }

    /** @return array<string, mixed> */
    public function ensureProfile(?string $identifier = null): array
    {
        $context = service('tenantContextManager')->current();
        $userId = $this->guard->userId();
        if (! $this->guard->check() || ! $context->isResolved() || $userId === null) {
            throw new InvalidArgumentException('Authenticated applicant and tenant context are required.');
        }

        $model = new ApplicantProfileModel();
        $profile = $model->where('user_id', $userId)->first();
        $payload = ['user_id' => $userId, 'status' => 'active'];
        if ($identifier !== null && trim($identifier) !== '') {
            $normalized = service('applicantIdentityNormalizer')->normalize($identifier);
            $payload[$normalized['type'] === 'email' ? 'email' : 'phone_e164'] = $normalized['value'];
        }

        if ($profile === null) {
            $id = (int) $model->insert($payload, true);
            service('auditLogger')->record('admissions.applicant.profile_created', ['target_type' => 'applicant_profile', 'target_id' => $id, 'summary' => 'Applicant profile created.']);

            return $model->find($id);
        }

        $model->update((int) $profile['id'], array_diff_key($payload, ['user_id' => true]));
        service('auditLogger')->record('admissions.applicant.profile_updated', ['target_type' => 'applicant_profile', 'target_id' => $profile['id'], 'summary' => 'Applicant profile updated.']);

        return $model->find((int) $profile['id']);
    }
}
