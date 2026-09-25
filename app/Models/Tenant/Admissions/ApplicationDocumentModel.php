<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Metadata-only model for private applicant documents stored below WRITEPATH. */
class ApplicationDocumentModel extends TenantScopedModel
{
    protected $table = 'application_documents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'applicant_profile_id', 'application_id', 'public_token', 'document_type',
        'original_name', 'storage_path', 'mime_type', 'extension', 'size_bytes', 'checksum_sha256',
        'storage_state', 'scan_status', 'scan_locked_at', 'scan_locked_by', 'scan_attempts', 'scan_error', 'scanned_at',
        'review_status', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
