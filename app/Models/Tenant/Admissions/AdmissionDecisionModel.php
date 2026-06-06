<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Current/historical tenant admissions decision for one application. */
class AdmissionDecisionModel extends TenantScopedModel
{
    protected $table = 'admission_decisions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'decision_type', 'decision_status', 'is_current', 'requires_approval', 'decided_by', 'approved_by', 'decision_reason', 'private_notes', 'decided_at', 'approved_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
