<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Auditable marker for Block 5; it never creates a student record in Block 3. */
class AdmissionConversionEligibilityMarkerModel extends TenantScopedModel
{
    protected $table = 'admission_conversion_eligibility_markers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'admission_offer_id', 'eligibility_status', 'marked_at', 'handoff_payload_json', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
