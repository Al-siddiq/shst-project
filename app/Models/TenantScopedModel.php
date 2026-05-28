<?php

namespace App\Models;

use App\Exceptions\TenantResolutionException;
use App\Services\Tenancy\TenantContextManager;
use CodeIgniter\Model;

abstract class TenantScopedModel extends Model
{
    /**
     * Applies tenant scope to all find operations.
     */
    protected function builder()
    {
        $builder = parent::builder();
        $tenantContext = service('tenantContextManager')->current();

        if (! $tenantContext->isResolved()) {
            throw new TenantResolutionException('Tenant context is required for tenant-scoped models.');
        }

        return $builder->where($this->table . '.tenant_id', $tenantContext->tenantId);
    }

    protected function beforeInsert(array $data)
    {
        $tenantContext = service('tenantContextManager')->current();

        if (! $tenantContext->isResolved()) {
            throw new TenantResolutionException('Tenant context is required before insert.');
        }

        $data['data']['tenant_id'] = $tenantContext->tenantId;

        return $data;
    }
}
