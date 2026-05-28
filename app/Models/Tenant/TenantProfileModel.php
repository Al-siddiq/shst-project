<?php

namespace App\Models\Tenant;

use App\Models\TenantScopedModel;

class TenantProfileModel extends TenantScopedModel
{
    protected $table = 'tenant_profiles';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'tenant_id', 'institution_name', 'short_name', 'official_email', 'official_phone',
        'address', 'state', 'lga', 'created_by', 'updated_by',
    ];
    protected $beforeInsert = ['beforeInsert'];
}
