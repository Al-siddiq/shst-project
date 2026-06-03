<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-scoped screening outcome record; decisions/offers remain later phases. */
class AdmissionScreeningRecordModel extends TenantScopedModel
{
    protected $table = 'admission_screening_records';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'screening_type', 'scheduled_at', 'held_at', 'venue', 'score', 'outcome', 'private_notes', 'decided_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
