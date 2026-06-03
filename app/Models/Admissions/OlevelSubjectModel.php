<?php

namespace App\Models\Admissions;

use CodeIgniter\Model;

/** Shared neutral O'Level subject reference; tenant requirements choose from it. */
class OlevelSubjectModel extends Model
{
    protected $table = 'olevel_subjects';
    protected $primaryKey = 'code';
    protected $returnType = 'array';
    protected $allowedFields = ['code', 'label', 'status', 'sort_order'];
}
