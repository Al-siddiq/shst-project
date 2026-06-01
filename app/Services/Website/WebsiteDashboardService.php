<?php

namespace App\Services\Website;

use App\Models\TenantDomainModel;
use App\Models\Tenant\Website\AdmissionInformationPageModel;
use App\Models\Tenant\Website\DepartmentPublicProfileModel;
use App\Models\Tenant\Website\GalleryAlbumModel;
use App\Models\Tenant\Website\GalleryItemModel;
use App\Models\Tenant\Website\MediaFileModel;
use App\Models\Tenant\Website\ProgrammePublicProfileModel;
use App\Models\Tenant\Website\WebsiteContentItemModel;
use App\Models\Tenant\Website\WebsiteMenuItemModel;

/** Builds tenant-only operational metrics and a practical website setup checklist. */
class WebsiteDashboardService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        $context = service('tenantContextManager')->current();
        if (! service('tenantAccess')->hasAuthority($context, 'website.dashboard.view')) {
            throw new \InvalidArgumentException('Required website dashboard authority is missing.');
        }
        $settings = service('websiteSettings')->publicSettings();
        $profileCounts = ['departments' => (new DepartmentPublicProfileModel())->where('status', 'published')->countAllResults(), 'programmes' => (new ProgrammePublicProfileModel())->where('status', 'published')->countAllResults()];
        $contentCounts = [];
        foreach (['draft', 'scheduled', 'published', 'archived'] as $status) {
            $contentCounts[$status] = (new WebsiteContentItemModel())->where('status', $status)->countAllResults();
        }
        $checklist = [
            'logo' => (new MediaFileModel())->where('category', 'tenant_logo')->where('visibility', 'public')->first() !== null,
            'contact_email' => ! empty($settings['contact_email']), 'contact_phone' => ! empty($settings['contact_phone']),
            'hero_content' => ! empty($settings['hero_title']) && ! empty($settings['hero_summary']),
            'admission_information' => (new AdmissionInformationPageModel())->where('status', 'published')->first() !== null,
            'menu_items' => (new WebsiteMenuItemModel())->where('status', 'active')->where('is_visible', 1)->first() !== null,
        ];
        $domain = (new TenantDomainModel())->where('tenant_id', $context->tenantId)->where('status', 'active')->where('is_primary', 1)->first();

        return [
            'enabled' => (int) ($settings['is_public_enabled'] ?? 0) === 1, 'publicUrl' => service('publicWebsiteUrl')->canonical('home'), 'primaryDomain' => $domain['domain'] ?? null,
            'checklist' => $checklist, 'completion' => (int) round(count(array_filter($checklist)) / count($checklist) * 100), 'contentCounts' => $contentCounts,
            'profileCounts' => $profileCounts, 'galleryCounts' => ['albums' => (new GalleryAlbumModel())->where('status', 'published')->countAllResults(), 'images' => (new GalleryItemModel())->where('status', 'published')->countAllResults()],
            'scheduledQueue' => (new WebsiteContentItemModel())->where('status', 'scheduled')->orderBy('scheduled_for')->findAll(10),
            'recentAudit' => service('websiteAudit')->recentForDashboard(8),
        ];
    }
}
