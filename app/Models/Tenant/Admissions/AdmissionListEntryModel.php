<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Safe public entry row for a published admission list. */
class AdmissionListEntryModel extends TenantScopedModel
{
    protected $table = 'admission_list_entries';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'admission_list_publication_id', 'applicant_application_id', 'admission_offer_id', 'application_number', 'applicant_display_name', 'programme_name', 'entry_position', 'entry_status', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
