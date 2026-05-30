<?php

namespace App\Controllers\Internal;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;

class TenantContextController extends BaseController
{
    use ApiResponseTrait;

    public function show()
    {
        $context = service('tenantContextManager')->current();

        return $this->ok('Tenant context resolved.', [
            'tenant_id' => $context->tenantId,
            'slug' => $context->slug,
            'source' => $context->source,
            'resolved' => $context->isResolved(),
        ]);
    }
}
