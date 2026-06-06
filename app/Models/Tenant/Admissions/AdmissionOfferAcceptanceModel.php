<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Applicant-owned offer acceptance/decline record; payment remains a placeholder. */
class AdmissionOfferAcceptanceModel extends TenantScopedModel
{
    protected $table = 'admission_offer_acceptances';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'admission_offer_id', 'acceptance_status', 'acceptance_fee_status', 'accepted_at', 'declined_at', 'applicant_comment', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
