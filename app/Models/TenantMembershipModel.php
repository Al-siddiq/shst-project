<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantMembershipModel extends Model
{
    protected $table            = 'tenant_memberships';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    // A tenant binding is permanent. Revocation changes lifecycle state but the
    // row remains queryable and the Shield user ID can never be rebound.
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'tenant_id',
        'user_id',
        'status',
        'membership_label',
        'is_active',
        'created_by',
        'updated_by',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
}
