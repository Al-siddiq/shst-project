<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-scoped Phase 1 admission configuration record. */
class AdmissionCycleModel extends TenantScopedModel
{
    protected $table = 'admission_cycles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'academic_session_id', 'title', 'code', 'opens_at', 'closes_at', 'status', 'is_public', 'allow_multiple_public_cycles', 'instructions', 'screening_instructions', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
