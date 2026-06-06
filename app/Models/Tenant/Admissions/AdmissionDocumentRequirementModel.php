<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-scoped Phase 1 admission configuration record. */
class AdmissionDocumentRequirementModel extends TenantScopedModel
{
    protected $table = 'admission_document_requirements';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'document_type', 'label', 'allowed_mime_types', 'maximum_size_bytes', 'is_required', 'status', 'sort_order', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
