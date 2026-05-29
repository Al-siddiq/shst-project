<?php

namespace App\Models;

class TenantThemeModel extends TenantScopedModel
{
    protected $table          = 'tenant_themes';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'tenant_id', 'primary_color', 'secondary_color', 'accent_color', 'logo_path', 'created_by', 'updated_by',
    ];
    protected $beforeInsert   = ['beforeInsert'];
}
