<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/** Public presentation extension for one tenant-owned Block 1 programme. */
class ProgrammePublicProfileModel extends TenantScopedModel
{
    protected $table = 'programme_public_profiles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'programme_id', 'slug', 'summary', 'body', 'entry_requirements',
        'career_opportunities', 'duration_explanation', 'award_type', 'admission_status',
        'featured_media_id', 'seo_title', 'seo_description', 'status', 'published_at',
        'published_by', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}
