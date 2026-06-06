<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Tenant-owned subject/grade row linked to one applicant O'Level sitting. */
class ApplicationOlevelResultModel extends TenantScopedModel
{
    protected $table = 'application_olevel_results';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'olevel_sitting_id', 'subject_code', 'subject_name', 'grade_code', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
