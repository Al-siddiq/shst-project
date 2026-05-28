<?php
namespace App\Models\Tenant;
use App\Models\TenantScopedModel;
class LevelModel extends TenantScopedModel
{
    protected $table = 'levels';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id','name','code','sort_order','is_active','created_by','updated_by'];
    protected $beforeInsert = ['beforeInsert'];
}
