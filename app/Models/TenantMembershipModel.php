<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantMembershipModel extends Model
{
    protected $table            = 'tenant_memberships';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'tenant_id',
        'user_id',
        'is_active',
        'is_default',
        'created_by',
        'updated_by',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
}
