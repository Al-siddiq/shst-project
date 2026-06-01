<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/** Tenant-scoped public leadership records; deliberately separate from future staff records. */
class ManagementProfileModel extends TenantScopedModel
{
    protected $table = 'management_profiles';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'full_name', 'title', 'bio', 'photo_media_id', 'sort_order', 'status',
        'published_at', 'published_by', 'archived_at', 'seo_title', 'seo_description', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}
