<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantMembershipHistoryModel extends Model
{
    protected $table = 'tenant_membership_history';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'tenant_membership_id', 'tenant_id', 'user_id', 'from_status', 'to_status',
        'reason', 'changed_by', 'created_at',
    ];
}
