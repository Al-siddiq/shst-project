<?php
namespace App\Models\Tenant;
use App\Models\TenantScopedModel;
class ProgrammeModel extends TenantScopedModel
{
    protected $table = 'programmes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id','department_id','name','code','duration_years','is_active','created_by','updated_by'];
    protected $beforeInsert = ['beforeInsert'];
}
