<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Server-side biodata draft; final immutable snapshots are Phase 3 work. */
class ApplicationBiodataDraftModel extends TenantScopedModel
{
    protected $table = 'application_biodata_drafts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'applicant_application_id', 'surname', 'first_name', 'other_names', 'gender', 'date_of_birth', 'phone_e164', 'email', 'residential_address', 'state_of_origin', 'lga_of_origin', 'nationality', 'marital_status', 'religion', 'next_of_kin_name', 'next_of_kin_phone_e164', 'guardian_name', 'guardian_phone_e164', 'completion_percent', 'last_saved_at', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
