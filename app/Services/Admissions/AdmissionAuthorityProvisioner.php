<?php

namespace App\Services\Admissions;

use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use InvalidArgumentException;

/** Idempotently provisions Block 3 authorities for an active tenant member. */
class AdmissionAuthorityProvisioner
{
    public function provisionTenantSuperAdmin(int $userId): void
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context, $userId)) {
            throw new InvalidArgumentException('User must be an active tenant member before admissions authority provisioning.');
        }

        foreach (AdmissionAuthorityCatalog::AUTHORITIES as $code => $name) {
            $authorityModel = new OperationalAuthorityModel();
            $authority = $authorityModel->where('code', $code)->first();
            $authorityId = $authority['id'] ?? $authorityModel->insert([
                'code' => $code,
                'name' => $name,
                'description' => 'Block 3 admissions authority for ' . $name . '.',
                'is_active' => 1,
            ], true);

            $grantModel = new MembershipAuthorityModel();
            $grant = $grantModel
                ->where('user_id', $userId)
                ->where('authority_id', $authorityId)
                ->where('scope_type', null)
                ->where('scope_id', null)
                ->first();

            if ($grant === null) {
                $grantModel->insert([
                    'user_id' => $userId,
                    'authority_id' => $authorityId,
                    'scope_type' => null,
                    'scope_id' => null,
                ]);
            }
        }

        service('auditLogger')->record('admissions.authorities.provisioned', [
            'target_type' => 'tenant_membership',
            'target_id' => $userId,
            'summary' => 'Admissions authorities provisioned for tenant super administrator.',
        ]);
    }
}
