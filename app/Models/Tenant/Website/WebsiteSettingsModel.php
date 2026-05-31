<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/**
 * Stores the single tenant-controlled public website settings record.
 *
 * Tenant profile data remains the institutional source of truth; this record
 * adds public-presentation copy and optional public contact overrides.
 */
class WebsiteSettingsModel extends TenantScopedModel
{
    protected $table = 'website_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'site_title', 'tagline', 'motto', 'hero_title', 'hero_summary', 'hero_media_id',
        'about_summary', 'about_body', 'mission', 'vision', 'history', 'contact_email', 'contact_phone',
        'contact_phone_alt', 'address', 'map_embed_url', 'portal_url', 'application_info_url',
        'application_cta_label', 'is_public_enabled', 'seo_title', 'seo_description', 'facebook_url',
        'instagram_url', 'x_url', 'youtube_url', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}
