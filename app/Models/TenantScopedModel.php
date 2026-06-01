<?php

namespace App\Models;

use App\Exceptions\TenantResolutionException;
use App\Libraries\Auth\IdentityGuard;
use CodeIgniter\Model;

/**
 * Base model for every record owned by exactly one tenant.
 *
 * Tenant isolation belongs below controllers so future callers, CLI commands,
 * and services cannot accidentally perform an unscoped lookup. Mutation
 * metadata is also applied here because actor fields must come from the trusted
 * authentication context rather than a browser payload.
 */
abstract class TenantScopedModel extends Model
{
    /** Every tenant-scoped model receives trusted update attribution by default. */
    protected $beforeUpdate = ['applyTenantUpdateMetadata'];

    /**
     * Applies tenant scope to all builder-backed operations.
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

    /**
     * Backward-compatible callback used by the original Block 1 models.
     * New models should use the more explicit applyTenantInsertMetadata name.
     */
    protected function beforeInsert(array $data): array
    {
        return $this->applyTenantInsertMetadata($data);
    }

    /**
     * Injects ownership and trusted actor metadata for tenant-owned inserts.
     */
    protected function applyTenantInsertMetadata(array $data): array
    {
        $tenantContext = service('tenantContextManager')->current();
        if (! $tenantContext->isResolved()) {
            throw new TenantResolutionException('Tenant context is required before insert.');
        }

        $data['data']['tenant_id'] = $tenantContext->tenantId;
        $actorId = (new IdentityGuard())->userId();
        if ($actorId !== null) {
            // Always overwrite browser-supplied actor fields when an authenticated
            // identity exists. This prevents forged audit attribution.
            $data['data']['created_by'] = $actorId;
            $data['data']['updated_by'] = $actorId;
        }

        return $data;
    }

    /**
     * Applies the trusted actor identity to updates. CI4 still owns updated_at
     * through useTimestamps, keeping one predictable timestamp mechanism.
     */
    protected function applyTenantUpdateMetadata(array $data): array
    {
        $tenantContext = service('tenantContextManager')->current();
        if (! $tenantContext->isResolved()) {
            throw new TenantResolutionException('Tenant context is required before update.');
        }

        $actorId = (new IdentityGuard())->userId();
        if ($actorId !== null) {
            $data['data']['updated_by'] = $actorId;
        }

        return $data;
    }
}
