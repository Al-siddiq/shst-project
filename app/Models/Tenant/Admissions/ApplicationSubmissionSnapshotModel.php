<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Immutable tenant-owned snapshot of an application at final submission time. */
class ApplicationSubmissionSnapshotModel extends TenantScopedModel
{
    protected $table = 'application_submission_snapshots';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'application_number', 'snapshot_json', 'submitted_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
