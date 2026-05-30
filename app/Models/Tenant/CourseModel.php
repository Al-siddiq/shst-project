<?php
namespace App\Models\Tenant;
use App\Models\TenantScopedModel;
class CourseModel extends TenantScopedModel
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id','department_id','title','course_code','credit_units','is_active','created_by','updated_by'];
    protected $beforeInsert = ['beforeInsert'];
}
