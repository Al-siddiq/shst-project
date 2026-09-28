<?php

namespace App\Services;

use App\Libraries\Auth\IdentityGuard;
use App\Models\AuditLogModel;
use App\Models\TenantDomainModel;
use App\Models\TenantModel;
use InvalidArgumentException;
use RuntimeException;

/** Platform-owned tenant lifecycle and domain registry. */
class PlatformTenantService
{
    private const STATUSES = ['pending_setup', 'active', 'suspended', 'deactivated', 'archived'];
    private const DOMAIN_TYPES = ['subdomain', 'custom_domain'];
    private const TRANSITIONS = ['pending_setup' => ['active', 'suspended', 'deactivated'], 'active' => ['suspended', 'deactivated'], 'suspended' => ['active', 'deactivated'], 'deactivated' => ['active', 'archived'], 'archived' => []];

    public function index(): array
    {
        return (new TenantModel())->orderBy('school_name', 'ASC')->findAll();
    }

    public function detail(int $tenantId): array
    {
        $tenant = (new TenantModel())->find($tenantId);
        if ($tenant === null) throw new InvalidArgumentException('Tenant was not found.');
        return [
            'tenant' => $tenant,
            'allowedTransitions' => self::TRANSITIONS[$tenant['status']] ?? [],
            'domains' => (new TenantDomainModel())->where('tenant_id', $tenantId)->orderBy('is_primary', 'DESC')->orderBy('domain')->findAll(),
            'audit' => (new AuditLogModel())->where('tenant_id', $tenantId)->where('context', 'platform')->orderBy('id', 'DESC')->findAll(30),
        ];
    }

    public function create(array $payload): int
    {
        return service('transactional')->run(function () use ($payload): int {
            $payload['slug'] = strtolower(trim((string) $payload['slug']));
            $payload['status'] = 'pending_setup';
            $payload['created_by'] = (new IdentityGuard())->userId();
            $payload['updated_by'] = (new IdentityGuard())->userId();
            $id = (new TenantModel())->insert($payload, true);
            if (! $id) throw new RuntimeException('Tenant could not be created.');
            service('auditLogger')->record('platform.tenant.created', ['tenant_id' => $id, 'context' => 'platform', 'target_type' => 'tenant', 'target_id' => $id, 'summary' => 'Platform administrator created tenant.']);
            return (int) $id;
        });
    }

    public function update(int $tenantId, array $payload): void
    {
        service('transactional')->run(function () use ($tenantId, $payload): array {
            $tenant = (new TenantModel())->find($tenantId);
            if ($tenant === null) throw new InvalidArgumentException('Tenant was not found.');
            unset($payload['status'], $payload['subscription_status']);
            $slugOwner = (new TenantModel())->where('slug', strtolower(trim((string) $payload['slug'])))->where('id !=', $tenantId)->first();
            if ($slugOwner !== null) throw new InvalidArgumentException('Tenant slug is already in use.');
            $payload['slug'] = strtolower(trim((string) $payload['slug']));
            $payload['updated_by'] = (new IdentityGuard())->userId();
            if (! (new TenantModel())->update($tenantId, $payload)) throw new RuntimeException('Tenant profile could not be updated.');
            service('auditLogger')->record('platform.tenant.updated', ['tenant_id' => $tenantId, 'context' => 'platform', 'target_type' => 'tenant', 'target_id' => $tenantId, 'summary' => 'Platform administrator updated tenant profile.']);
            return [];
        });
    }

    public function transition(int $tenantId, string $status, string $reason): void
    {
        if (! in_array($status, self::STATUSES, true) || mb_strlen(trim($reason)) < 10) throw new InvalidArgumentException('A supported lifecycle status and meaningful reason are required.');
        service('transactional')->run(function () use ($tenantId, $status, $reason): array {
            $tenant = (new TenantModel())->find($tenantId);
            if ($tenant === null) throw new InvalidArgumentException('Tenant was not found.');
            if ($tenant['status'] === $status) return [];
            if (! in_array($status, self::TRANSITIONS[$tenant['status']] ?? [], true)) throw new InvalidArgumentException('Requested tenant lifecycle transition is not allowed.');
            if (! (new TenantModel())->update($tenantId, ['status' => $status, 'updated_by' => (new IdentityGuard())->userId()])) throw new RuntimeException('Tenant lifecycle could not be updated.');
            service('auditLogger')->record('platform.tenant.lifecycle_changed', ['tenant_id' => $tenantId, 'context' => 'platform', 'target_type' => 'tenant', 'target_id' => $tenantId, 'summary' => 'Platform administrator changed tenant lifecycle.', 'metadata' => ['from' => $tenant['status'], 'to' => $status, 'reason' => trim($reason)]]);
            return [];
        });
    }

    public function saveDomain(int $tenantId, array $payload): int
    {
        $submitted = strtolower(rtrim(trim((string) ($payload['domain'] ?? '')), '.'));
        $domain = function_exists('idn_to_ascii') ? idn_to_ascii($submitted, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) : $submitted;
        $type = (string) ($payload['type'] ?? 'custom_domain');
        if (! is_string($domain) || preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain) !== 1 || ! in_array($type, self::DOMAIN_TYPES, true)) {
            throw new InvalidArgumentException('Provide a valid hostname and domain type.');
        }
        return service('transactional')->run(function () use ($tenantId, $payload, $domain, $type): int {
            if ((new TenantModel())->find($tenantId) === null) throw new InvalidArgumentException('Tenant was not found.');
            $model = new TenantDomainModel();
            $existing = $model->where('domain', $domain)->withDeleted()->first();
            if ($existing !== null && (int) $existing['tenant_id'] !== $tenantId) throw new InvalidArgumentException('Hostname is already assigned to another tenant.');
            if (! empty($payload['is_primary'])) $model->where('tenant_id', $tenantId)->set(['is_primary' => 0])->update();
            $data = ['tenant_id' => $tenantId, 'domain' => $domain, 'type' => $type, 'is_primary' => ! empty($payload['is_primary']) ? 1 : 0, 'status' => 'active', 'updated_by' => (new IdentityGuard())->userId()];
            $id = $existing === null ? $model->insert(array_merge($data, ['created_by' => (new IdentityGuard())->userId()]), true) : $existing['id'];
            if ($existing !== null && ! $model->update((int) $id, $data)) throw new RuntimeException('Tenant domain could not be updated.');
            if (! $id) throw new RuntimeException('Tenant domain could not be saved.');
            service('auditLogger')->record('platform.tenant.domain_saved', ['tenant_id' => $tenantId, 'context' => 'platform', 'target_type' => 'tenant_domain', 'target_id' => $id, 'summary' => 'Platform administrator saved tenant domain.', 'metadata' => ['domain' => $domain, 'type' => $type]]);
            return (int) $id;
        });
    }
}
