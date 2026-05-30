<?php

namespace App\Models;

class TenantIamGroupAssignmentModel extends TenantScopedModel
{
    protected $table          = 'tenant_iam_group_assignments';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = ['tenant_id', 'user_id', 'group_name', 'created_by', 'updated_by'];
    protected $beforeInsert   = ['beforeInsert'];
}
