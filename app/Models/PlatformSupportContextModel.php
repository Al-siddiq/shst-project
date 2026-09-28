<?php

namespace App\Models;

use CodeIgniter\Model;

class PlatformSupportContextModel extends Model
{
    protected $table = 'platform_support_contexts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'public_token', 'platform_user_id', 'tenant_id', 'reason', 'access_mode',
        'status', 'started_at', 'expires_at', 'ended_at', 'ip_address', 'user_agent',
    ];
}
