<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-owned membership row for one application in a shortlist batch. */
class AdmissionShortlistEntryModel extends TenantScopedModel
{
    protected $table = 'admission_shortlist_entries';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'shortlist_batch_id', 'applicant_application_id', 'entry_status', 'rank_position', 'private_notes', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
