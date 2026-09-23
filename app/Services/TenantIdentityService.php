<?php

namespace App\Services;

use App\Entities\TenantContext;
use App\Libraries\Auth\IdentityGuard;
use App\Models\TenantMembershipModel;
use App\Models\TenantMembershipHistoryModel;
use App\Models\TenantModel;
use InvalidArgumentException;
use RuntimeException;

/** Enforces the permanent one-Shield-user-to-one-tenant binding. */
class TenantIdentityService
{
    public function __construct(private readonly IdentityGuard $guard = new IdentityGuard())
    {
    }

    /** @return array<string, mixed>|null */
    public function membershipForUser(?int $userId): ?array
    {
        if ($userId === null) {
            return null;
        }

        return (new TenantMembershipModel())->where('user_id', $userId)->first();
    }

    /** @return array<string, mixed> */
    public function bindUser(int $userId, TenantContext $context, string $label = 'Tenant account'): array
    {
        if (! $context->isResolved()) {
            throw new InvalidArgumentException('A resolved tenant is required before an account can be bound.');
        }
        if ($this->guard->isPlatformAdministrator() && $userId === $this->guard->userId()) {
            throw new InvalidArgumentException('Platform administrators cannot receive tenant membership.');
        }

        $existing = $this->membershipForUser($userId);
        if ($existing !== null) {
            if ((int) $existing['tenant_id'] !== $context->tenantId) {
                throw new InvalidArgumentException('This account is permanently bound to another tenant. Create a separate account for this school.');
            }
            if (($existing['status'] ?? '') !== 'active' || (int) ($existing['is_active'] ?? 0) !== 1) {
                throw new InvalidArgumentException('This tenant account is not active.');
            }

            return $existing;
        }

        $tenant = (new TenantModel())->find($context->tenantId);
        if ($tenant === null || ! in_array($tenant['status'], ['active', 'pending_setup'], true)) {
            throw new InvalidArgumentException('This tenant cannot accept account provisioning.');
        }

        $db = db_connect();
        $db->transStart();
        $id = (int) (new TenantMembershipModel())->insert([
            'tenant_id' => $context->tenantId,
            'user_id' => $userId,
            'status' => 'active',
            'membership_label' => trim($label) ?: 'Tenant account',
            'is_active' => 1,
            'created_by' => $this->guard->userId(),
            'updated_by' => $this->guard->userId(),
        ], true);

        service('auditLogger')->record('tenant.membership.bound', [
            'target_type' => 'tenant_membership',
            'target_id' => $id,
            'summary' => 'Shield identity permanently bound to tenant.',
            'metadata' => ['bound_user_id' => $userId],
        ]);
        (new TenantMembershipHistoryModel())->insert([
            'tenant_membership_id' => $id,
            'tenant_id' => $context->tenantId,
            'user_id' => $userId,
            'from_status' => null,
            'to_status' => 'active',
            'reason' => 'Initial permanent tenant binding.',
            'changed_by' => $this->guard->userId(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $db->transComplete();
        if (! $db->transStatus()) {
            throw new RuntimeException('The tenant account binding could not be committed.');
        }

        return (new TenantMembershipModel())->find($id);
    }

    public function assertContextMatchesCurrentUser(TenantContext $context): array
    {
        $userId = $this->guard->userId();
        $membership = $this->membershipForUser($userId);
        if ($membership === null || ! $context->isResolved()
            || (int) $membership['tenant_id'] !== $context->tenantId
            || ($membership['status'] ?? '') !== 'active'
            || (int) ($membership['is_active'] ?? 0) !== 1) {
            throw new InvalidArgumentException('The authenticated account does not belong to this tenant.');
        }

        return $membership;
    }
}
