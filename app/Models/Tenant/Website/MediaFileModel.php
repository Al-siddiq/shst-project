<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/**
 * Tenant-scoped catalogue for uploaded website media.
 *
 * The model deliberately exposes metadata only. Physical file operations live
 * in MediaService so path generation and visibility checks cannot be bypassed.
 */
class MediaFileModel extends TenantScopedModel
{
    protected $table = 'media_files';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'original_name', 'stored_name', 'storage_disk', 'storage_path',
        'mime_type', 'extension', 'size_bytes', 'width', 'height', 'visibility',
        'category', 'checksum_sha256', 'derivatives', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
