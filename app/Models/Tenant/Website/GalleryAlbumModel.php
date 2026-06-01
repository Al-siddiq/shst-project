<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/** Tenant-scoped gallery group with publication metadata and a public cover image. */
class GalleryAlbumModel extends TenantScopedModel
{
    protected $table = 'gallery_albums';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'title', 'slug', 'description', 'category', 'cover_media_id', 'sort_order',
        'status', 'published_at', 'published_by', 'archived_at', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}
