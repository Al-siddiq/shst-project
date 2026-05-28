<?php
namespace App\Models\Tenant;
use App\Models\TenantScopedModel;
class ProgrammeCourseModel extends TenantScopedModel
{
    protected $table = 'programme_courses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['tenant_id','programme_id','course_id','level_id','semester_id','is_required','created_by','updated_by'];
    protected $beforeInsert = ['beforeInsert'];
}
