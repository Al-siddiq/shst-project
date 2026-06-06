<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-owned shortlist/waitlist/rejection batch prepared before offers. */
class AdmissionShortlistBatchModel extends TenantScopedModel
{
    protected $table = 'admission_shortlist_batches';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'batch_code', 'title', 'batch_type', 'status', 'private_notes', 'finalized_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
