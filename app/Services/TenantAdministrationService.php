<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\DepartmentIdentityModel;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use App\Models\Tenant\AcademicSessionModel;
use App\Models\Tenant\CourseModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\LevelModel;
use App\Models\Tenant\ProgrammeCourseModel;
use App\Models\Tenant\ProgrammeModel;
use App\Models\Tenant\SemesterModel;
use App\Models\Tenant\TenantProfileModel;
use App\Models\TenantDomainModel;
use App\Models\TenantMembershipHistoryModel;
use App\Models\TenantMembershipModel;
use App\Models\TenantThemeModel;
use InvalidArgumentException;
use RuntimeException;

/** Read models and trusted mutations for the Block 1 tenant-admin product. */
class TenantAdministrationService
{
    public function dashboard(): array
    {
        $context = service('tenantContextManager')->current();
        return [
            'profile' => (new TenantProfileModel())->first(), 'theme' => service('themeResolver')->tenantTheme(),
            'metrics' => ['sessions' => (new AcademicSessionModel())->countAllResults(), 'departments' => (new DepartmentModel())->countAllResults(), 'programmes' => (new ProgrammeModel())->countAllResults(), 'courses' => (new CourseModel())->countAllResults(), 'members' => (new TenantMembershipModel())->where('tenant_id', $context->tenantId)->where('status', 'active')->countAllResults()],
            'navigation' => service('navigationResolver')->tenantItems(),
        ];
    }

    public function configuration(): array
    {
        return ['profile' => (new TenantProfileModel())->first(), 'sessions' => (new AcademicSessionModel())->orderBy('name')->findAll(), 'semesters' => (new SemesterModel())->orderBy('name')->findAll(), 'levels' => (new LevelModel())->orderBy('sort_order')->findAll(), 'departments' => (new DepartmentModel())->orderBy('name')->findAll(), 'programmes' => (new ProgrammeModel())->orderBy('name')->findAll(), 'courses' => (new CourseModel())->orderBy('course_code')->findAll(), 'mappings' => (new ProgrammeCourseModel())->findAll()];
    }

    public function access(): array
    {
        $context = service('tenantContextManager')->current();
        return ['memberships' => (new TenantMembershipModel())->where('tenant_id', $context->tenantId)->orderBy('id')->findAll(), 'authorities' => (new OperationalAuthorityModel())->orderBy('name')->findAll(), 'grants' => (new MembershipAuthorityModel())->orderBy('user_id')->findAll()];
    }

    public function theme(): array
    {
        return ['theme' => service('themeResolver')->tenantTheme(), 'departments' => (new DepartmentModel())->orderBy('name')->findAll(), 'identities' => (new DepartmentIdentityModel())->findAll()];
    }

    public function domains(): array
    {
        $context = service('tenantContextManager')->current();
        return (new TenantDomainModel())->where('tenant_id', $context->tenantId)->orderBy('is_primary', 'DESC')->findAll();
    }

    public function audit(): array
    {
        $context = service('tenantContextManager')->current();
        return (new AuditLogModel())->where('tenant_id', $context->tenantId)->orderBy('id', 'DESC')->findAll(100);
    }

    public function createAuthority(array $payload): int
    {
        return service('transactional')->run(function () use ($payload): int {
            $id = (new OperationalAuthorityModel())->insert(['code' => strtolower(trim($payload['code'])), 'name' => trim($payload['name']), 'description' => trim($payload['description'] ?? '') ?: null, 'is_active' => 1], true);
            if (! $id) throw new RuntimeException('Authority could not be created.');
            service('auditLogger')->record('tenant.operational_authority.created', ['target_type' => 'operational_authority', 'target_id' => $id, 'summary' => 'Tenant administrator created operational authority.']);
            return (int) $id;
        });
    }

    public function grantAuthority(int $membershipId, int $authorityId): int
    {
        $context = service('tenantContextManager')->current();
        $membership = (new TenantMembershipModel())->where('tenant_id', $context->tenantId)->where('status', 'active')->find($membershipId);
        $authority = (new OperationalAuthorityModel())->find($authorityId);
        if ($membership === null || $authority === null) throw new InvalidArgumentException('Select an active member and valid authority.');
        return service('transactional')->run(function () use ($membership, $authorityId): int {
            $model = new MembershipAuthorityModel();
            $existing = $model->where('user_id', $membership['user_id'])->where('authority_id', $authorityId)->first();
            if ($existing !== null) return (int) $existing['id'];
            $id = $model->insert(['user_id' => $membership['user_id'], 'authority_id' => $authorityId], true);
            if (! $id) throw new RuntimeException('Authority grant could not be saved.');
            service('auditLogger')->record('tenant.operational_authority.granted', ['target_type' => 'membership_authority', 'target_id' => $id, 'summary' => 'Tenant administrator granted operational authority.', 'metadata' => ['user_id' => $membership['user_id'], 'authority_id' => $authorityId]]);
            return (int) $id;
        });
    }

    public function transitionMembership(int $membershipId, string $status, string $reason): void
    {
        if (! in_array($status, ['active', 'suspended', 'revoked'], true) || mb_strlen(trim($reason)) < 10) throw new InvalidArgumentException('Select a lifecycle status and provide a meaningful reason.');
        $context = service('tenantContextManager')->current();
        $membership = (new TenantMembershipModel())->where('tenant_id', $context->tenantId)->find($membershipId);
        if ($membership === null) throw new InvalidArgumentException('Tenant membership was not found.');
        if ((int) $membership['user_id'] === service('tenantAccess')->currentUserId() && $status !== 'active') throw new InvalidArgumentException('You cannot suspend or revoke your own administrative account.');
        service('transactional')->run(function () use ($membership, $status, $reason): array {
            if ($membership['status'] === $status) return [];
            $active = $status === 'active' ? 1 : 0;
            if (! (new TenantMembershipModel())->update($membership['id'], ['status' => $status, 'is_active' => $active])) throw new RuntimeException('Membership lifecycle could not be updated.');
            (new TenantMembershipHistoryModel())->insert(['tenant_membership_id' => $membership['id'], 'tenant_id' => $membership['tenant_id'], 'user_id' => $membership['user_id'], 'from_status' => $membership['status'], 'to_status' => $status, 'reason' => trim($reason), 'changed_by' => service('tenantAccess')->currentUserId(), 'created_at' => date('Y-m-d H:i:s')]);
            service('auditLogger')->record('tenant.membership.lifecycle_changed', ['target_type' => 'tenant_membership', 'target_id' => $membership['id'], 'summary' => 'Tenant administrator changed account lifecycle.', 'metadata' => ['from' => $membership['status'], 'to' => $status, 'reason' => trim($reason)]]);
            return [];
        });
    }

    public function saveTheme(array $payload): void
    {
        service('transactional')->run(function () use ($payload): array {
            $model = new TenantThemeModel(); $existing = $model->first();
            $id = $existing === null ? $model->insert($payload, true) : $existing['id'];
            if ($existing !== null) $model->update($id, $payload);
            if (! $id) throw new RuntimeException('Theme could not be saved.');
            service('auditLogger')->record('tenant.theme.saved', ['target_type' => 'tenant_theme', 'target_id' => $id, 'summary' => 'Tenant administrator updated school branding.']);
            return [];
        });
    }

    public function saveDepartmentIdentity(array $payload): void
    {
        if ((new DepartmentModel())->find((int) $payload['department_id']) === null) throw new InvalidArgumentException('Select a valid department.');
        service('transactional')->run(function () use ($payload): array {
            $model = new DepartmentIdentityModel(); $existing = $model->where('department_id', $payload['department_id'])->first();
            $id = $existing === null ? $model->insert($payload, true) : $existing['id'];
            if ($existing !== null) $model->update($id, $payload);
            if (! $id) throw new RuntimeException('Department identity could not be saved.');
            service('auditLogger')->record('department.identity.saved', ['target_type' => 'department_identity', 'target_id' => $id, 'summary' => 'Tenant administrator updated department identity.']);
            return [];
        });
    }
}
