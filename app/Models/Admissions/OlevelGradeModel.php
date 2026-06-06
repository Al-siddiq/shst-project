<?php

namespace App\Models\Admissions;

use CodeIgniter\Model;

/** Shared neutral O'Level grade reference used by submission validation. */
class OlevelGradeModel extends Model
{
    protected $table = 'olevel_grades';
    protected $primaryKey = 'code';
    protected $returnType = 'array';
    protected $allowedFields = ['code', 'label', 'rank_value', 'is_passing', 'status', 'sort_order'];
}
