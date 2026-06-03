<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Immutable document-review decision log for tenant admissions staff. */
class AdmissionDocumentReviewLogModel extends TenantScopedModel
{
    protected $table = 'admission_document_review_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'application_document_id', 'decision', 'reviewer_user_id', 'private_notes', 'applicant_message', 'reviewed_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
