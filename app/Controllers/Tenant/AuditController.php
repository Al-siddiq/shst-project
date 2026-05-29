<?php

namespace App\Controllers\Tenant;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Traits\ApiResponseTrait;

class AuditController extends BaseController
{
    use ApiResponseTrait;

    public function index()
    {
        $context = service('tenantContextManager')->current();
        $records = (new AuditLogModel())
            ->where('tenant_id', $context->tenantId)
            ->orderBy('id', 'DESC')
            ->findAll(50);

        return $this->ok('Audit logs loaded.', ['items' => $records]);
    }
}
