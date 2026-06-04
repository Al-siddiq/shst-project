<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-owned active/historical admission offer. Acceptance waits for Phase 7. */
class AdmissionOfferModel extends TenantScopedModel
{
    protected $table = 'admission_offers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'admission_decision_id', 'offer_reference', 'offer_status', 'offered_programme_opening_id', 'offer_snapshot_json', 'offer_letter_template_key', 'issued_at', 'expires_at', 'revoked_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
