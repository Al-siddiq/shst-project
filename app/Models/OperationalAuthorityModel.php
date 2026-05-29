<?php

namespace App\Models;

class OperationalAuthorityModel extends TenantScopedModel
{
    protected $table          = 'operational_authorities';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['tenant_id', 'code', 'name', 'description', 'is_active', 'created_by', 'updated_by'];
    protected $beforeInsert   = ['beforeInsert'];
}
