<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Private tenant-staff application review and correction-window record. */
class AdmissionApplicationReviewModel extends TenantScopedModel
{
    protected $table = 'admission_application_reviews';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'review_status', 'reviewer_user_id', 'private_notes', 'public_correction_message', 'correction_allowed_until', 'reviewed_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
