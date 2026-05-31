<?php

namespace App\Services\Website;

use App\Entities\TenantContext;
use App\Models\TenantModel;
use App\Models\Tenant\Website\WebsiteSettingsModel;

/**
 * Decides whether resolved tenant data is safe to expose anonymously.
 *
 * Resolution and publication eligibility are separate concerns: internal setup
 * screens may need a suspended tenant context, while public pages must never
 * disclose content for an inactive school. The Phase 1 launch switch adds a
 * second explicit opt-in before anonymous website delivery becomes available.
 */
class PublicTenantGuard
{
    public function allows(TenantContext $context): bool
    {
        if (! $context->isResolved()) {
            return false;
        }

        $tenant = (new TenantModel())->find($context->tenantId);

        if ($tenant === null || $tenant['status'] !== 'active') {
            return false;
        }

        // Phase 1 adds an explicit launch switch. A school must opt in before
        // anonymous pages or media are exposed, even when its tenant is active.
        $settings = (new WebsiteSettingsModel())->first();

        return $settings !== null && (int) $settings['is_public_enabled'] === 1;
    }
}
