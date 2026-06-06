<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-scoped Phase 1 admission configuration record. */
class AdmissionProgrammeOpeningModel extends TenantScopedModel
{
    protected $table = 'admission_programme_openings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'admission_cycle_id', 'programme_id', 'department_id', 'entry_level_id', 'application_quota', 'screening_method', 'instructions', 'requirement_summary', 'status', 'sort_order', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
