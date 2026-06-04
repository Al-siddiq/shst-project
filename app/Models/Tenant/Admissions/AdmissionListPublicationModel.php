<?php

namespace App\Models\Tenant\Admissions;

use App\Models\TenantScopedModel;

/** Versioned tenant-owned admission list publication header. */
class AdmissionListPublicationModel extends TenantScopedModel
{
    protected $table = 'admission_list_publications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id', 'admission_cycle_id', 'programme_opening_id', 'title', 'version_number', 'public_token', 'status', 'safe_fields_json', 'export_hook', 'private_notes', 'published_at', 'published_by', 'created_by', 'updated_by'];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];
}
