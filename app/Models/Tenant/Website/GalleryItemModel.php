<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/** Ordered tenant-owned album image reference; physical media remains owned by MediaService. */
class GalleryItemModel extends TenantScopedModel
{
    protected $table = 'gallery_items';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'gallery_album_id', 'media_file_id', 'caption', 'alt_text', 'sort_order',
        'status', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}
