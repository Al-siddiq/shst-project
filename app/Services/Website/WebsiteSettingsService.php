<?php

namespace App\Services\Website;

use App\Models\Tenant\TenantProfileModel;
use App\Models\Tenant\Website\WebsiteSettingsModel;

/**
 * Resolves public settings with neutral, tenant-database fallbacks only.
 *
 * No school identity is embedded in code. Missing CMS overrides fall back to
 * the tenant profile established in Block 1, allowing an enabled school to
 * launch a useful shell without duplicating institutional contact data.
 */
class WebsiteSettingsService
{
    /** @return array<string, mixed> */
    public function publicSettings(): array
    {
        return service('publicWebsiteCache')->remember('settings', function (): array {
            $profile = (new TenantProfileModel())->first() ?? [];
            $settings = (new WebsiteSettingsModel())->first() ?? [];

            return array_merge($settings, [
                'site_title' => $settings['site_title'] ?? $profile['institution_name'] ?? '',
                'short_name' => $profile['short_name'] ?? $settings['site_title'] ?? '',
                'contact_email' => $settings['contact_email'] ?? $profile['official_email'] ?? null,
                'contact_phone' => $settings['contact_phone'] ?? $profile['official_phone'] ?? null,
                'address' => $settings['address'] ?? $profile['address'] ?? null,
                'state' => $profile['state'] ?? null,
                'lga' => $profile['lga'] ?? null,
                'application_cta_label' => $settings['application_cta_label'] ?? 'Admission Information',
            ]);
        });
    }
}
