<?php

namespace App\Models;

class DepartmentIdentityModel extends TenantScopedModel
{
    protected $table          = 'department_identities';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'tenant_id', 'department_id', 'color_hex', 'icon_key', 'created_by', 'updated_by',
    ];
    protected $beforeInsert   = ['beforeInsert'];
}
