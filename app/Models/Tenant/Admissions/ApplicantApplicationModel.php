<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-owned draft application shell created before Phase 3 submission. */
class ApplicantApplicationModel extends TenantScopedModel
{
    protected $table = 'applicant_applications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_profile_id', 'admission_cycle_id', 'programme_opening_id', 'public_token', 'application_number', 'status', 'biodata_status', 'started_at', 'last_saved_at', 'submitted_at', 'submission_snapshot_id', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
