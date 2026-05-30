<?php

namespace App\Models;

class MembershipAuthorityModel extends TenantScopedModel
{
    protected $table          = 'membership_authorities';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'tenant_id', 'user_id', 'authority_id', 'scope_type', 'scope_id', 'created_by', 'updated_by',
    ];
    protected $beforeInsert   = ['beforeInsert'];
}
