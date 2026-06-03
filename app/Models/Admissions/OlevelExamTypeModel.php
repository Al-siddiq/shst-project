<?php

namespace App\Models\Admissions;

use CodeIgniter\Model;

/** Shared neutral O'Level exam-type reference; never tenant-specific. */
class OlevelExamTypeModel extends Model
{
    protected $table = 'olevel_exam_types';
    protected $primaryKey = 'code';
    protected $returnType = 'array';
    protected $allowedFields = ['code', 'label', 'status', 'sort_order'];
}
