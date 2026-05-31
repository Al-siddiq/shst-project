<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/** Public presentation extension for one tenant-owned Block 1 department. */
class DepartmentPublicProfileModel extends TenantScopedModel
{
    protected $table = 'department_public_profiles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'department_id', 'slug', 'summary', 'body', 'featured_media_id',
        'seo_title', 'seo_description', 'status', 'published_at', 'published_by',
        'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}
