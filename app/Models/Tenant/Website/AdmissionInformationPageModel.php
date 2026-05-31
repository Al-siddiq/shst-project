<?php

namespace App\Models\Tenant\Website;

use App\Models\TenantScopedModel;

/** Tenant-controlled public admission guidance; never an application record. */
class AdmissionInformationPageModel extends TenantScopedModel
{
    protected $table = 'admission_information_pages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'title', 'slug', 'summary', 'body', 'admission_status', 'requirements_body',
        'application_fee_note', 'screening_information', 'required_documents', 'important_dates',
        'how_to_apply_body', 'application_link_label', 'application_url', 'seo_title',
        'seo_description', 'status', 'published_at', 'published_by', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['applyTenantInsertMetadata'];
}
