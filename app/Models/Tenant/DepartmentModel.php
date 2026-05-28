<?php
namespace App\Models\Tenant;
use App\Models\TenantScopedModel;
class DepartmentModel extends TenantScopedModel
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id','name','code','color_hex','is_active','created_by','updated_by'];
    protected $beforeInsert = ['beforeInsert'];
}
