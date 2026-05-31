<?php

namespace App\Services\Website;

use App\Entities\TenantContext;
use App\Models\TenantModel;

/**
 * Decides whether resolved tenant data is safe to expose anonymously.
 *
 * Resolution and publication eligibility are separate concerns: internal setup
 * screens may need a suspended tenant context, while public pages must never
 * disclose content for an inactive school. Phase 1 will extend this guard with
 * the tenant website-enabled setting after that table is introduced.
 */
class PublicTenantGuard
{
    public function allows(TenantContext $context): bool
    {
        if (! $context->isResolved()) {
            return false;
        }

        $tenant = (new TenantModel())->find($context->tenantId);

        return $tenant !== null && $tenant['status'] === 'active';
    }
}
