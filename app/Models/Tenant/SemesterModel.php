<?php
namespace App\Models\Tenant;
use App\Models\TenantScopedModel;
class SemesterModel extends TenantScopedModel
{
    protected $table = 'semesters';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id','name','code','is_active','created_by','updated_by'];
    protected $beforeInsert = ['beforeInsert'];
}
