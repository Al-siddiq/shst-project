<?php
namespace App\Models\Tenant;
use App\Models\TenantScopedModel;
class AcademicSessionModel extends TenantScopedModel
{
    protected $table = 'academic_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id','name','start_date','end_date','is_current','status','created_by','updated_by'];
    protected $beforeInsert = ['beforeInsert'];
}
