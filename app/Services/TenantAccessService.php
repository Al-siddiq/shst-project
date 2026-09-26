<?php

namespace App\Services;

use App\Entities\TenantContext;
use App\Libraries\Auth\IdentityGuard;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use App\Models\TenantMembershipModel;

class TenantAccessService
{
    /** @var list<string> */
    private const STAFF_GROUPS = ['tenant_super_admin', 'tenant_admin', 'lecturer'];

    public function __construct(private readonly IdentityGuard $guard = new IdentityGuard())
    {
    }

    public function currentUserId(): ?int
    {
        return $this->guard->userId();
    }

    public function activeMembership(TenantContext $context, ?int $userId = null): ?array
    {
        $userId ??= $this->currentUserId();
        if (! $context->isResolved() || $userId === null) {
            return null;
        }

        return (new TenantMembershipModel())
            ->where('tenant_id', $context->tenantId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->first();
    }

    public function isMember(TenantContext $context, ?int $userId = null): bool
    {
        return $this->activeMembership($context, $userId) !== null;
    }

    public function groups(TenantContext $context, ?int $userId = null): array
    {
        $userId ??= $this->currentUserId();
        if (! $context->isResolved() || $userId === null || $userId !== $this->currentUserId()
            || $this->activeMembership($context, $userId) === null) {
            return [];
        }

        return $this->guard->groups();
    }

    public function hasGroup(TenantContext $context, string $group, ?int $userId = null): bool
    {
        return in_array($group, $this->groups($context, $userId), true);
    }

    public function authorities(TenantContext $context, ?int $userId = null): array
    {
        $userId ??= $this->currentUserId();
        if (! $context->isResolved() || $userId === null || $this->activeMembership($context, $userId) === null) {
            return [];
        }

        $grantTable = (new MembershipAuthorityModel())->getTable();
        $authorityTable = (new OperationalAuthorityModel())->getTable();

        $records = (new MembershipAuthorityModel())
            ->select($authorityTable . '.code')
            ->join($authorityTable, $authorityTable . '.id = ' . $grantTable . '.authority_id')
            ->where($grantTable . '.user_id', $userId)
            ->where($authorityTable . '.is_active', 1)
            ->findAll();

        return array_values(array_map(static fn (array $row): string => $row['code'], $records));
    }

    public function hasAuthority(TenantContext $context, string $authority, ?int $userId = null): bool
    {
        return array_intersect(self::STAFF_GROUPS, $this->groups($context, $userId)) !== []
            && in_array($authority, $this->authorities($context, $userId), true);
    }
}
