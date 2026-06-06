<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Minimal tenant-staff clearance placeholder for accepted applicants. */
class AdmissionClearanceStatusModel extends TenantScopedModel
{
    protected $table = 'admission_clearance_statuses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'admission_offer_id', 'clearance_status', 'acceptance_fee_status', 'staff_notes', 'updated_by_staff_id', 'cleared_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
