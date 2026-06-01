<?php

namespace App\Services\Website;

use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use InvalidArgumentException;

/**
 * Provisions Block 2 authorities for a tenant super administrator.
 *
 * Operational authorities are tenant records rather than global constants in
 * the database. This service turns the stable product catalogue into scoped
 * records and grants, allowing onboarding and later support tools to share one
 * idempotent implementation.
 */
class WebsiteAuthorityProvisioner
{
    public function provisionTenantSuperAdmin(int $userId): void
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->isMember($context, $userId)) {
            throw new InvalidArgumentException('User must be an active tenant member before website authority provisioning.');
        }

        foreach (WebsiteAuthorityCatalog::AUTHORITIES as $code => $name) {
            $authorityModel = new OperationalAuthorityModel();
            $authority = $authorityModel->where('code', $code)->first();
            $authorityId = $authority['id'] ?? $authorityModel->insert([
                'code' => $code,
                'name' => $name,
                'description' => 'Block 2 website authority for ' . $name . '.',
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

        service('auditLogger')->record('website.authorities.provisioned', [
            'target_type' => 'tenant_membership',
            'target_id' => $userId,
            'summary' => 'Website authorities provisioned for tenant super administrator.',
        ]);
    }
}
