<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-scoped Phase 1 admission configuration record. */
class AdmissionRequirementDefinitionModel extends TenantScopedModel
{
    protected $table = 'admission_requirement_definitions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'requirement_type', 'code', 'label', 'description', 'is_required', 'configuration_json', 'status', 'sort_order', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
