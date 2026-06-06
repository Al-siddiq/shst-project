<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-scoped Phase 1 admission configuration record. */
class AdmissionSubjectRequirementModel extends TenantScopedModel
{
    protected $table = 'admission_subject_requirements';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'subject_code', 'subject_name', 'minimum_grade', 'requirement_group', 'is_required', 'status', 'sort_order', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
