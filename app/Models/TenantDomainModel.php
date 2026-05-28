<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantDomainModel extends Model
{
    protected $table            = 'tenant_domains';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'tenant_id',
        'domain',
        'type',
        'is_primary',
        'status',
        'created_by',
        'updated_by',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
}
