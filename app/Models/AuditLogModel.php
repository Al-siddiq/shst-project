<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table              = 'audit_logs';
    protected $primaryKey         = 'id';
    protected $returnType         = 'array';
    protected $useAutoIncrement   = true;
    protected $useTimestamps      = false;
    protected $allowedFields      = [
        'tenant_id', 'actor_user_id', 'context', 'action', 'target_type', 'target_id',
        'summary', 'metadata', 'ip_address', 'user_agent', 'created_at',
    ];
    protected bool $allowEmptyInserts = false;
}
